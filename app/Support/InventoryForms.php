<?php

namespace App\Support;

class InventoryForms
{
    /** @return array<string, string> */
    public static function names(): array
    {
        $names = [
            'aircon' => 'Air Conditioning Units',
            'ceilings' => 'Ceilings',
            'chairs' => 'Chairs',
            'circuit-breakers' => 'Circuit Breakers',
            'doors' => 'Doors',
            'electric-fan' => 'Electric Fan',
            'faucets' => 'Faucets',
            'lightings' => 'Lightings',
            'septic-tanks' => 'Septic Tanks',
            'service-entrance' => 'Service Entrance',
            'shelves' => 'Shelves',
            'tables' => 'Tables',
            'water-closets' => 'Water Closets',
            'water-motor' => 'Water Motor',
            'water-pipes' => 'Water Pipes',
            'water-tank' => 'Water Tank',
            'windows' => 'Windows',
            'convenience-outlet' => 'Convenience Outlet',
            'exhaust-fan' => 'Exhaust Fan',
            'floor-drains' => 'Floor Drains',
            'lavatories' => 'Lavatories',
            'service-meter' => 'Service Meter',
            'walls' => 'Walls',
        ];

        asort($names);

        return $names;
    }

    /** @return array{code: string, rows: int} */
    public static function pdfLayout(string $form): array
    {
        abort_unless(array_key_exists($form, self::names()), 404);

        return match ($form) {
            'convenience-outlet' => ['code' => 'FM-AD-ENG-09d', 'rows' => 30],
            'exhaust-fan' => ['code' => 'FM-AD-ENG-09b', 'rows' => 30],
            'floor-drains' => ['code' => 'FM-AD-ENG-10c', 'rows' => 22],
            'lavatories' => ['code' => 'FM-AD-ENG-10d', 'rows' => 22],
            'service-meter' => ['code' => 'FM-AD-ENG-09d', 'rows' => 30],
            'walls' => ['code' => 'FM-AD-ENG-11c', 'rows' => 24],
            default => ['code' => '', 'rows' => 18],
        };
    }

    /** @return array<string, array<string, array{label: string, type: string, required: bool}>> */
    public static function sections(string $form): array
    {
        abort_unless(array_key_exists($form, self::names()), 404);

        $itemLabel = match ($form) {
            'aircon' => 'Air Conditioner No.',
            'ceilings' => 'Ceiling No.',
            'chairs' => 'Chair No.',
            'circuit-breakers' => 'Circuit Breaker No.',
            'doors' => 'Door No.',
            'faucets' => 'Faucet No.',
            'lightings' => 'Light No.',
            'septic-tanks' => 'Septic Tank No.',
            'shelves' => 'Shelf No.',
            'tables' => 'Table No.',
            'water-closets' => 'Water Closet No.',
            'water-pipes' => 'Water Pipe No.',
            'windows' => 'Window No.',
            'lavatories' => 'Lavatory / Sink No.',
            'walls' => 'Wall No.',
            default => self::names()[$form].' No.',
        };
        $sections = ['Location and item' => [
            'building_name' => self::field('Building Name', required: true),
            'room' => self::field(in_array($form, ['walls', 'ceilings', 'chairs', 'shelves', 'tables'], true) ? 'Room No.' : 'Room / Office', required: true),
            'item_number' => self::field($itemLabel, required: true),
        ]];

        if (in_array($form, ['walls', 'doors', 'windows'], true)) {
            $sections['Location and item'] += [
                'height' => self::field('Height (m)', 'number', true),
                'width' => self::field('Width (m)', 'number', true),
                'material' => self::field('Material', required: true),
            ];
        }

        if ($form === 'ceilings') {
            $sections['Location and item']['area'] = self::field('Area', 'number', true);
        }

        if (in_array($form, ['chairs', 'shelves', 'tables'], true)) {
            $sections['Location and item']['material'] = self::field('Material', required: true);
        }

        $actions = match ($form) {
            'floor-drains', 'lavatories', 'water-closets' => ['cleaned', 'declogged', 'replaced'],
            'walls' => ['repaired', 'repainted', 'replaced'],
            'ceilings' => ['repaired', 'replaced', 'repainted'],
            'septic-tanks' => ['repaired', 'declogged'],
            'chairs', 'doors', 'faucets', 'shelves', 'tables', 'water-motor', 'water-pipes', 'water-tank', 'windows' => ['repaired', 'replaced'],
            default => ['cleaned', 'repaired', 'replaced'],
        };

        foreach ([1, 2] as $inspection) {
            $prefix = 'inspection_'.$inspection.'_';
            $fields = [
                $prefix.'date' => self::field('Inspection Date', 'date'),
                $prefix.'condition' => self::field('Condition', required: true),
            ];
            foreach ($actions as $action) {
                $fields[$prefix.$action] = self::field(ucfirst($action), 'checkbox');
            }
            $fields[$prefix.'inspected_by'] = self::field('Inspected By', required: true);
            $sections['Inspection '.$inspection.' and maintenance'] = $fields;
        }

        return $sections;
    }

    /** @return array{label: string, type: string, required: bool} */
    private static function field(string $label, string $type = 'text', bool $required = false): array
    {
        return compact('label', 'type', 'required');
    }
}
