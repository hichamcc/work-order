# Truck oil service status API

Read-only endpoint so another system (e.g. the delivery site) can colour truck
numbers by how close they are to an oil service.

## Authentication

Every request needs the shared token from `INTEGRATION_API_TOKEN`, sent as
either header:

```
Authorization: Bearer <token>
X-Api-Token: <token>
```

Missing or wrong token returns `401`.

## Endpoints

### All tracked trucks

```
GET /api/trucks/service-status
GET /api/trucks/service-status?flagged=1
```

`flagged=1` returns only the red and yellow trucks, which is usually all the
delivery system needs.

```json
{
  "generated_at": "2026-09-15T09:09:46+00:00",
  "interval_km": 120000,
  "due_soon_km": 10000,
  "count": 6,
  "trucks": {
    "DW69754/PWT10/8937": {
      "truck_number": "DW69754/PWT10/8937",
      "tracked": true,
      "state": "overdue",
      "colour": "red",
      "current_km": 542162,
      "due_at_km": 527551,
      "km_remaining": -14611,
      "last_checked_at": "2026-09-07T15:01:54+00:00"
    }
  }
}
```

### One truck

```
GET /api/trucks/service-status/DW69754%2FPWT10%2F8937
GET /api/trucks/service-status/DW69754
```

Both forms work: the full plate, or just the leading segment. A truck that is
not tracked returns `404` with `"tracked": false`.

## Colours

| `state` | `colour` | Meaning |
|---------|----------|---------|
| `overdue` | `red` | At or past the service point |
| `due_soon` | `yellow` | Within 10,000 km of it |
| `upcoming` | `null` | Further out; no highlight |

`km_remaining` is negative once a truck is overdue.

## Client example (core PHP)

Poll every 30 minutes and cache to a local file, so the delivery site keeps
working when the workshop system is unreachable.

```php
<?php

function truckServiceColours(): array
{
    $cacheFile = __DIR__ . '/cache/truck-colours.json';
    $cacheTtl  = 1800; // 30 minutes

    // Serve from cache while it is fresh.
    if (is_readable($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTtl) {
        $cached = json_decode(file_get_contents($cacheFile), true);
        if (is_array($cached)) {
            return $cached;
        }
    }

    $ch = curl_init('https://workshop.example.com/api/trucks/service-status?flagged=1');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . TRUCK_API_TOKEN],
    ]);
    $raw    = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($raw !== false && $status === 200) {
        $data = json_decode($raw, true);

        if (!empty($data['trucks'])) {
            $colours = [];

            foreach ($data['trucks'] as $plate => $truck) {
                // Index by both forms so either plate format matches locally.
                $key = strtoupper(preg_replace('/\s+/', '', $plate));
                $colours[$key] = $truck['colour'];

                foreach (['/', '-'] as $separator) {
                    if (str_contains($key, $separator)) {
                        $colours[explode($separator, $key)[0]] = $truck['colour'];
                        break;
                    }
                }
            }

            @mkdir(dirname($cacheFile), 0775, true);
            file_put_contents($cacheFile, json_encode($colours));

            return $colours;
        }
    }

    // The workshop system is down: fall back to a stale cache rather than
    // losing the highlighting altogether.
    if (is_readable($cacheFile)) {
        $stale = json_decode(file_get_contents($cacheFile), true);
        if (is_array($stale)) {
            return $stale;
        }
    }

    return [];
}

function truckColour(string $plate, array $colours): ?string
{
    return $colours[strtoupper(preg_replace('/\s+/', '', $plate))] ?? null;
}
```

Using it in a page:

```php
<?php $colours = truckServiceColours(); ?>

<?php foreach ($deliveries as $delivery): ?>
    <?php $colour = truckColour($delivery['truck'], $colours); ?>

    <td class="<?= $colour === 'red' ? 'truck-red' : ($colour === 'yellow' ? 'truck-yellow' : '') ?>"
        <?= $colour ? 'title="Oil service ' . ($colour === 'red' ? 'overdue' : 'due soon') . '"' : '' ?>>
        <?= htmlspecialchars($delivery['truck']) ?>
    </td>
<?php endforeach; ?>
```

```css
.truck-red    { background: #fee2e2; color: #991b1b; font-weight: 600; }
.truck-yellow { background: #fef3c7; color: #92400e; font-weight: 600; }
```

## Notes

- Data changes nightly at 04:00, plus whenever someone records a service or
  edits a due point, so a 30 minute poll is plenty.
- Always cache. If the workshop system is down, the delivery site should keep
  working with the last known colours.
- Keep the token out of version control on the delivery side too.
