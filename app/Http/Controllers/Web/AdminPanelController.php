<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminPanelController extends Controller
{
    public function show(string $module = 'dashboard'): View
    {
        $allowedModules = [
            'dashboard',
            'vendors',
            'catalog',
            'orders',
            'payments',
            'users',
            'help-center',
            'settings',
        ];

        abort_unless(in_array($module, $allowedModules, true), 404);

        return view('admin.panel', [
            'module' => $module,
            'panelTitle' => __('panels.admin.panel_title'),
            'moduleTitle' => __('panels.admin.modules.'.$module),
            'moduleMeta' => __('panels.pattern.meta.'.$this->metaKeyFor($module)),
            'navItems' => $this->navigation(),
            'summaryCards' => $this->summaryCardsFor($module),
            'tableColumns' => $this->tableColumnsFor($module),
            'tableRows' => $this->tableRowsFor($module),
            'timeline' => $this->timelineFor($module),
            'formFields' => $this->formFieldsFor($module),
            'statusPill' => $this->statusPillFor($module),
            'vendorsPendingRoute' => route('admin.web.vendors.pending'),
            'emptyState' => [
                'title' => __('panels.pattern.empty.title'),
                'reason' => __('panels.pattern.empty.reason'),
                'cta' => __('panels.pattern.empty.cta'),
            ],
        ]);
    }

    public function pendingVendors(): View
    {
        $vendors = User::query()
            ->with('vendorProfile')
            ->where('user_type_id', UserType::VENDOR)
            ->where('user_status_id', UserStatus::PENDING)
            ->orderByDesc('id')
            ->paginate(10);

        return view('admin.vendors.pending', [
            'module' => 'vendors',
            'panelTitle' => __('panels.admin.panel_title'),
            'moduleTitle' => __('panels.admin.pending_vendors_title'),
            'moduleMeta' => __('panels.admin.pending_vendors_meta'),
            'navItems' => $this->navigation(),
            'statusPill' => ['label' => __('panels.status.pending'), 'tone' => 'warning'],
            'vendors' => $vendors,
        ]);
    }

    public function showPendingVendor(User $vendor): View
    {
        abort_unless($vendor->isType(UserType::VENDOR), 404);
        abort_unless((int) $vendor->user_status_id === UserStatus::PENDING, 404);

        $vendor->load('vendorProfile');

        return view('admin.vendors.show', [
            'module' => 'vendors',
            'panelTitle' => __('panels.admin.panel_title'),
            'moduleTitle' => __('panels.admin.pending_vendor_detail_title'),
            'moduleMeta' => __('panels.admin.pending_vendor_detail_meta'),
            'navItems' => $this->navigation(),
            'statusPill' => ['label' => __('panels.status.pending'), 'tone' => 'warning'],
            'vendor' => $vendor,
        ]);
    }

    public function approvePendingVendor(User $vendor): RedirectResponse
    {
        abort_unless($vendor->isType(UserType::VENDOR), 404);
        abort_unless((int) $vendor->user_status_id === UserStatus::PENDING, 422);

        $vendor->update([
            'user_status_id' => UserStatus::ACTIVE,
        ]);

        $vendor->vendorProfile()?->update([
            'is_verified' => true,
        ]);

        return redirect()->route('admin.web.vendors.pending')->with('status', __('panels.admin.vendor_approved'));
    }

    public function rejectPendingVendor(User $vendor): RedirectResponse
    {
        abort_unless($vendor->isType(UserType::VENDOR), 404);
        abort_unless((int) $vendor->user_status_id === UserStatus::PENDING, 422);

        $vendor->update([
            'user_status_id' => UserStatus::INACTIVE,
        ]);

        $vendor->vendorProfile()?->update([
            'is_verified' => false,
        ]);

        return redirect()->route('admin.web.vendors.pending')->with('status', __('panels.admin.vendor_rejected'));
    }

    /**
     * @return array<int, array{href: string, label: string, module: string}>
     */
    private function navigation(): array
    {
        return [
            ['href' => route('admin.dashboard'), 'label' => __('panels.admin.modules.dashboard'), 'module' => 'dashboard'],
            ['href' => route('admin.module', ['module' => 'vendors']), 'label' => __('panels.admin.modules.vendors'), 'module' => 'vendors'],
            ['href' => route('admin.module', ['module' => 'catalog']), 'label' => __('panels.admin.modules.catalog'), 'module' => 'catalog'],
            ['href' => route('admin.module', ['module' => 'orders']), 'label' => __('panels.admin.modules.orders'), 'module' => 'orders'],
            ['href' => route('admin.module', ['module' => 'payments']), 'label' => __('panels.admin.modules.payments'), 'module' => 'payments'],
            ['href' => route('admin.module', ['module' => 'users']), 'label' => __('panels.admin.modules.users'), 'module' => 'users'],
            ['href' => route('admin.module', ['module' => 'help-center']), 'label' => __('panels.admin.modules.help-center'), 'module' => 'help-center'],
            ['href' => route('admin.module', ['module' => 'settings']), 'label' => __('panels.admin.modules.settings'), 'module' => 'settings'],
        ];
    }

    private function metaKeyFor(string $module): string
    {
        return match ($module) {
            'dashboard' => 'detail',
            'settings' => 'form',
            default => 'data',
        };
    }

    /**
     * @return array<int, array{label: string, value: string, tone: string}>
     */
    private function summaryCardsFor(string $module): array
    {
        if ($module === 'vendors') {
            return [
                ['label' => __('panels.metrics.total_vendors'), 'value' => (string) User::query()->where('user_type_id', UserType::VENDOR)->count(), 'tone' => 'neutral'],
                ['label' => __('panels.metrics.pending'), 'value' => (string) User::query()->where('user_type_id', UserType::VENDOR)->where('user_status_id', UserStatus::PENDING)->count(), 'tone' => 'warning'],
                ['label' => __('panels.metrics.approved'), 'value' => (string) User::query()->where('user_type_id', UserType::VENDOR)->where('user_status_id', UserStatus::ACTIVE)->count(), 'tone' => 'success'],
                ['label' => __('panels.metrics.rejected'), 'value' => (string) User::query()->where('user_type_id', UserType::VENDOR)->where('user_status_id', UserStatus::INACTIVE)->count(), 'tone' => 'error'],
            ];
        }

        return [
            ['label' => __('panels.metrics.total'), 'value' => '128', 'tone' => 'neutral'],
            ['label' => __('panels.metrics.pending'), 'value' => '14', 'tone' => 'warning'],
            ['label' => __('panels.metrics.approved'), 'value' => '96', 'tone' => 'success'],
            ['label' => __('panels.metrics.flagged'), 'value' => '18', 'tone' => 'error'],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function tableColumnsFor(string $module): array
    {
        if ($module === 'vendors') {
            return [
                __('panels.table.columns.name'),
                __('panels.table.columns.owner'),
                __('panels.table.columns.updated'),
                __('panels.table.columns.status'),
            ];
        }

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
        if ($module === 'vendors') {
            return User::query()
                ->with('vendorProfile')
                ->where('user_type_id', UserType::VENDOR)
                ->where('user_status_id', UserStatus::PENDING)
                ->orderByDesc('id')
                ->limit(5)
                ->get()
                ->map(static function (User $vendor): array {
                    return [
                        'name' => trim($vendor->first_name.' '.$vendor->last_name),
                        'owner' => $vendor->vendorProfile?->business_name ?? '-',
                        'updated' => optional($vendor->updated_at)->format('Y-m-d H:i') ?? '-',
                        'status' => 'warning',
                    ];
                })
                ->values()
                ->all();
        }

        return [
            ['name' => 'Catalog A', 'owner' => 'Team A', 'updated' => '09:00', 'status' => 'success'],
            ['name' => 'Catalog B', 'owner' => 'Team B', 'updated' => '10:30', 'status' => 'warning'],
            ['name' => 'Catalog C', 'owner' => 'Team C', 'updated' => '11:45', 'status' => 'info'],
        ];
    }

    /**
     * @return array<int, array{title: string, time: string, detail: string}>
     */
    private function timelineFor(string $module): array
    {
        return [
            ['title' => __('panels.timeline.reviewed'), 'time' => '08:30', 'detail' => __('panels.timeline.reviewed_detail')],
            ['title' => __('panels.timeline.synced'), 'time' => '10:10', 'detail' => __('panels.timeline.synced_detail')],
            ['title' => __('panels.timeline.notified'), 'time' => '11:40', 'detail' => __('panels.timeline.notified_detail')],
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
                'help-center' => 'info',
                'vendors' => 'warning',
                default => 'success',
            },
        ];
    }
}
