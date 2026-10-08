<?php

namespace App\Support;

class InventoryForms
{
    /** @return array<string, string> */
    public static function names(): array
    {
        return [
            'convenience-outlet' => 'Convenience Outlet',
            'exhaust-fan' => 'Exhaust Fan',
            'floor-drains' => 'Floor Drains',
            'lavatories' => 'Lavatories',
            'service-meter' => 'Service Meter',
            'walls' => 'Walls',
        ];
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
        };
    }

    /** @return array<string, array<string, array{label: string, type: string, required: bool}>> */
    public static function sections(string $form): array
    {
        abort_unless(array_key_exists($form, self::names()), 404);

        $itemLabel = match ($form) {
            'lavatories' => 'Lavatory / Sink No.',
            'walls' => 'Wall No.',
            default => self::names()[$form].' No.',
        };
        $sections = ['Location and item' => [
            'building_name' => self::field('Building Name', required: true),
            'room' => self::field($form === 'walls' ? 'Room No.' : 'Room / Office', required: true),
            'item_number' => self::field($itemLabel, required: true),
        ]];

        if ($form === 'walls') {
            $sections['Location and item'] += [
                'height' => self::field('Height (m)', 'number', true),
                'width' => self::field('Width (m)', 'number', true),
                'material' => self::field('Material', required: true),
            ];
        }

        $actions = match ($form) {
            'floor-drains', 'lavatories' => ['cleaned', 'declogged', 'replaced'],
            'walls' => ['repaired', 'repainted', 'replaced'],
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
