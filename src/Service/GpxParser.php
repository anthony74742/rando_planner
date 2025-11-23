<?php

namespace App\Service;

use DateTimeImmutable;
use SimpleXMLElement;

final class GpxParser
{
    /**
     * @return array{points: list<array{lat: float, lng: float}>, distance_km: float|null, duration_hours: float|null}|null
     */
    public function parse(string $path): ?array
    {
        if (!is_readable($path)) {
            return null;
        }

        $previousSetting = libxml_use_internal_errors(true);
        $xml = simplexml_load_file($path);
        libxml_clear_errors();
        libxml_use_internal_errors($previousSetting);

        if (!$xml instanceof SimpleXMLElement) {
            return null;
        }

        $pointNodes = $this->collectTrackPoints($xml);
        if (empty($pointNodes)) {
            return null;
        }

        $points = [];
        $distance = 0.0;
        $previous = null;
        $startTime = null;
        $endTime = null;

        foreach ($pointNodes as $node) {
            $lat = isset($node['lat']) ? (float) $node['lat'] : null;
            $lng = isset($node['lon']) ? (float) $node['lon'] : null;

            if (null === $lat || null === $lng) {
                continue;
            }

            $time = isset($node->time) ? $this->createDateTime((string) $node->time) : null;

            if ($previous) {
                $distance += $this->haversineDistance($previous['lat'], $previous['lng'], $lat, $lng);
            }

            if (!$startTime && $time) {
                $startTime = $time;
            }

            if ($time) {
                $endTime = $time;
            }

            $points[] = [
                'lat' => $lat,
                'lng' => $lng,
            ];

            $previous = ['lat' => $lat, 'lng' => $lng];
        }

        if (count($points) < 2) {
            return null;
        }

        $durationHours = null;
        if ($startTime && $endTime) {
            $seconds = max(0, $endTime->getTimestamp() - $startTime->getTimestamp());
            $durationHours = $seconds / 3600;
        }

        return [
            'points' => array_values($points),
            'distance_km' => $distance / 1000,
            'duration_hours' => $durationHours,
        ];
    }

    /**
     * @return list<SimpleXMLElement>
     */
    private function collectTrackPoints(SimpleXMLElement $xml): array
    {
        $points = [];

        if (isset($xml->trk)) {
            foreach ($xml->trk as $track) {
                if (!isset($track->trkseg)) {
                    continue;
                }

                foreach ($track->trkseg as $segment) {
                    if (!isset($segment->trkpt)) {
                        continue;
                    }

                    foreach ($segment->trkpt as $trkpt) {
                        $points[] = $trkpt;
                    }
                }
            }
        }

        if (empty($points) && isset($xml->rte)) {
            foreach ($xml->rte as $route) {
                if (!isset($route->rtept)) {
                    continue;
                }

                foreach ($route->rtept as $pt) {
                    $points[] = $pt;
                }
            }
        }

        if (empty($points) && isset($xml->wpt)) {
            foreach ($xml->wpt as $pt) {
                $points[] = $pt;
            }
        }

        return $points;
    }

    private function haversineDistance(float $latFrom, float $lonFrom, float $latTo, float $lonTo): float
    {
        $earthRadius = 6371000; // meters

        $latFromRad = deg2rad($latFrom);
        $latToRad = deg2rad($latTo);
        $latDelta = deg2rad($latTo - $latFrom);
        $lonDelta = deg2rad($lonTo - $lonFrom);

        $angle = 2 * asin(sqrt(
            pow(sin($latDelta / 2), 2) +
            cos($latFromRad) * cos($latToRad) * pow(sin($lonDelta / 2), 2)
        ));

        return $angle * $earthRadius;
    }

    private function createDateTime(string $value): ?DateTimeImmutable
    {
        try {
            return new DateTimeImmutable($value);
        } catch (\Exception) {
            return null;
        }
    }
}
