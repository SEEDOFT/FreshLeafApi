<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\View\View;

class VendorPanelController extends Controller
{
    public function show(string $module = 'dashboard'): View
    {
        $allowedModules = [
            'dashboard',
            'products',
            'orders',
            'payments',
            'store-profile',
            'notifications',
            'help',
        ];

        abort_unless(in_array($module, $allowedModules, true), 404);

        return view('vendor.panel', [
            'module' => $module,
            'panelTitle' => __('panels.vendor.panel_title'),
            'moduleTitle' => __('panels.vendor.modules.'.$module),
            'moduleMeta' => __('panels.pattern.meta.'.$this->metaKeyFor($module)),
            'navItems' => $this->navigation(),
            'summaryCards' => $this->summaryCardsFor($module),
            'tableColumns' => $this->tableColumnsFor($module),
            'tableRows' => $this->tableRowsFor($module),
            'timeline' => $this->timelineFor($module),
            'formFields' => $this->formFieldsFor($module),
            'statusPill' => $this->statusPillFor($module),
            'emptyState' => [
                'title' => __('panels.pattern.empty.title'),
                'reason' => __('panels.pattern.empty.reason'),
                'cta' => __('panels.pattern.empty.cta'),
            ],
        ]);
    }

    /**
     * @return array<int, array{href: string, label: string, module: string}>
     */
    private function navigation(): array
    {
        return [
            ['href' => route('vendor.dashboard'), 'label' => __('panels.vendor.modules.dashboard'), 'module' => 'dashboard'],
            ['href' => route('vendor.module', ['module' => 'products']), 'label' => __('panels.vendor.modules.products'), 'module' => 'products'],
            ['href' => route('vendor.module', ['module' => 'orders']), 'label' => __('panels.vendor.modules.orders'), 'module' => 'orders'],
            ['href' => route('vendor.module', ['module' => 'payments']), 'label' => __('panels.vendor.modules.payments'), 'module' => 'payments'],
            ['href' => route('vendor.module', ['module' => 'store-profile']), 'label' => __('panels.vendor.modules.store-profile'), 'module' => 'store-profile'],
            ['href' => route('vendor.module', ['module' => 'notifications']), 'label' => __('panels.vendor.modules.notifications'), 'module' => 'notifications'],
            ['href' => route('vendor.module', ['module' => 'help']), 'label' => __('panels.vendor.modules.help'), 'module' => 'help'],
        ];
    }

    private function metaKeyFor(string $module): string
    {
        return match ($module) {
            'dashboard' => 'detail',
            'store-profile' => 'form',
            default => 'data',
        };
    }

    /**
     * @return array<int, array{label: string, value: string, tone: string}>
     */
    private function summaryCardsFor(string $module): array
    {
        return [
            ['label' => __('panels.metrics.total'), 'value' => '84', 'tone' => 'neutral'],
            ['label' => __('panels.metrics.pending'), 'value' => '9', 'tone' => 'warning'],
            ['label' => __('panels.metrics.approved'), 'value' => '67', 'tone' => 'success'],
            ['label' => __('panels.metrics.flagged'), 'value' => '8', 'tone' => 'error'],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function tableColumnsFor(string $module): array
    {
        return [
            __('panels.table.columns.name'),
            __('panels.table.columns.owner'),
            __('panels.table.columns.updated'),
            __('panels.table.columns.status'),
        ];
    }

    /**
     * @return array<int, array{name: string, owner: string, updated: string, status: string}>
     */
    private function tableRowsFor(string $module): array
    {
        $prefix = Str::title(str_replace('-', ' ', $module));

        return [
            ['name' => $prefix.' A', 'owner' => 'Store A', 'updated' => '09:20', 'status' => 'success'],
            ['name' => $prefix.' B', 'owner' => 'Store B', 'updated' => '10:50', 'status' => 'warning'],
            ['name' => $prefix.' C', 'owner' => 'Store C', 'updated' => '12:00', 'status' => 'info'],
        ];
    }

    /**
     * @return array<int, array{title: string, time: string, detail: string}>
     */
    private function timelineFor(string $module): array
    {
        return [
            ['title' => __('panels.timeline.reviewed'), 'time' => '07:40', 'detail' => __('panels.timeline.reviewed_detail')],
            ['title' => __('panels.timeline.synced'), 'time' => '09:35', 'detail' => __('panels.timeline.synced_detail')],
            ['title' => __('panels.timeline.notified'), 'time' => '11:05', 'detail' => __('panels.timeline.notified_detail')],
        ];
    }

    /**
     * @return array<int, array{label: string, placeholder: string, type: string}>
     */
    private function formFieldsFor(string $module): array
    {
        return [
            ['label' => __('panels.form.fields.name'), 'placeholder' => __('panels.form.placeholders.name'), 'type' => 'text'],
            ['label' => __('panels.form.fields.email'), 'placeholder' => __('panels.form.placeholders.email'), 'type' => 'email'],
            ['label' => __('panels.form.fields.phone'), 'placeholder' => __('panels.form.placeholders.phone'), 'type' => 'tel'],
            ['label' => __('panels.form.fields.note'), 'placeholder' => __('panels.form.placeholders.note'), 'type' => 'text'],
        ];
    }

    /**
     * @return array{label: string, tone: string}
     */
    private function statusPillFor(string $module): array
    {
        return [
            'label' => __('panels.status.active'),
            'tone' => match ($module) {
                'payments' => 'warning',
                'help' => 'info',
                default => 'success',
            },
        ];
    }
}
