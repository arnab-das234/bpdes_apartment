@props([
    'items' => [],
    'transactions' => [],
    'proposals' => [],
    'projects' => [],
    'milestones' => [],
    'showInventoryModal' => false,
    'inventoryModalMode' => 'create',
    'editingItemId' => null,
    'invName' => '',
    'invSku' => '',
    'invCategory' => 'Electrical',
    'invUnit' => 'pcs',
    'invMinStock' => 5,
    'invUnitCost' => 0.00,
    'invAllocatedBudget' => 0.00,
    'invStorageLocation' => 'Main Store Room',
    'transType' => 'Purchase',
    'transQuantity' => 1,
    'transUnitPrice' => 0.00,
    'transProposalId' => null,
    'transProjectId' => null,
    'transMilestoneId' => null,
    'transRemarks' => '',
])

@php
    $totalValuation = $items->sum(fn($i) => $i->stock_quantity * $i->unit_cost);
    $totalReservedBudget = $items->sum('allocated_budget');
    $lowStockCount = $items->filter(fn($i) => $i->stock_quantity <= $i->min_stock_level)->count();
@endphp

<div class="space-y-6">
    <!-- Top Compact Header & KPI Bar -->
    <div class="bg-gradient-to-r from-slate-100 via-sky-50 to-slate-100 rounded-2xl p-5 text-slate-900 shadow-sm border border-slate-300 relative overflow-hidden">
        <div class="absolute right-0 top-0 w-64 h-64 bg-sky-200/30 rounded-full blur-3xl pointer-events-none"></div>
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-slate-200 text-slate-800 border border-slate-300">
                        Organization Stock Vault
                    </span>
                    <span class="text-slate-600 text-xs font-semibold">• Dedicated Budget Control</span>
                </div>
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 mt-1 tracking-tight">📦 Inventory & Stock Reserve Desk</h2>
                <p class="text-slate-600 text-xs font-medium mt-0.5">Manage physical stock levels, record purchases, issue items for building projects, and reserve stocks with dedicated budgets.</p>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                <button wire:click="openCreateInventoryItemModal" 
                        class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-black font-extrabold text-xs shadow-md transition-all flex items-center gap-2 cursor-pointer border border-slate-800">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>+ Add New Item</span>
                </button>
            </div>
        </div>

        <!-- 4-Grid KPI Stats Bar -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-5 pt-4 border-t border-slate-300">
            <div class="bg-white/90 p-3 rounded-xl border border-slate-300/80 shadow-2xs">
                <span class="text-[10px] font-extrabold text-slate-500 uppercase tracking-wider block">Total Inventory Valuation</span>
                <strong class="text-lg font-black text-emerald-700 font-mono block mt-0.5">
                    {{ format_indian_currency($totalValuation) }}
                </strong>
                <span class="text-[9px] text-slate-500 font-semibold block mt-0.5">Across {{ $items->count() }} Stock Items</span>
            </div>

            <div class="bg-white/90 p-3 rounded-xl border border-slate-300/80 shadow-2xs">
                <span class="text-[10px] font-extrabold text-slate-500 uppercase tracking-wider block">Reserved Stock Budget</span>
                <strong class="text-lg font-black text-amber-800 font-mono block mt-0.5">
                    {{ format_indian_currency($totalReservedBudget) }}
                </strong>
                <span class="text-[9px] text-slate-500 font-semibold block mt-0.5">Allocated for Planned Purchases</span>
            </div>

            <div class="bg-white/90 p-3 rounded-xl border border-slate-300/80 shadow-2xs">
                <span class="text-[10px] font-extrabold text-slate-500 uppercase tracking-wider block">Active Stock Items</span>
                <strong class="text-lg font-black text-slate-900 block mt-0.5">
                    {{ $items->count() }} Items
                </strong>
                <span class="text-[9px] text-slate-500 font-semibold block mt-0.5">In {{ $items->pluck('category')->unique()->count() }} Categories</span>
            </div>

            <div class="bg-white/90 p-3 rounded-xl border {{ $lowStockCount > 0 ? 'border-rose-400 bg-rose-50/60' : 'border-slate-300/80' }} shadow-2xs">
                <span class="text-[10px] font-extrabold {{ $lowStockCount > 0 ? 'text-rose-700' : 'text-slate-500' }} uppercase tracking-wider block">Low Stock Warnings</span>
                <strong class="text-lg font-black {{ $lowStockCount > 0 ? 'text-rose-800 animate-pulse' : 'text-slate-800' }} block mt-0.5">
                    {{ $lowStockCount }} Alert{{ $lowStockCount === 1 ? '' : 's' }}
                </strong>
                <span class="text-[9px] {{ $lowStockCount > 0 ? 'text-rose-700 font-bold' : 'text-slate-500 font-semibold' }} block mt-0.5">
                    {{ $lowStockCount > 0 ? 'Requires Stock Reorder' : 'All Items Sufficient' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Filter & Search Controls Bar -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div class="flex items-center gap-2 flex-1 min-w-0">
            <div class="relative flex-1">
                <input type="text" wire:model.live.debounce.300ms="inventorySearch" 
                       placeholder="Search by item name, SKU code, location..." 
                       class="w-full pl-9 pr-4 py-2 rounded-xl text-xs border border-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-slate-50/50">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Category:</span>
            <select wire:model.live="inventoryCategoryFilter" 
                    class="py-2 px-3 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white focus:ring-2 focus:ring-indigo-500">
                <option value="all">All Categories</option>
                <option value="Electrical">Electrical</option>
                <option value="Plumbing">Plumbing</option>
                <option value="Cleaning">Cleaning & Sanitation</option>
                <option value="Security">Security & CCTV</option>
                <option value="Hardware">Hardware & Construction</option>
                <option value="Office">Office & Admin</option>
            </select>
        </div>
    </div>

    <!-- Flash Notification -->
    @if(session()->has('message'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-bold flex items-center justify-between shadow-2xs">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('message') }}</span>
            </div>
        </div>
    @endif

    <!-- Main Inventory Data Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-200 bg-slate-50/70 flex items-center justify-between">
            <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider">Stock Inventory Register</h3>
            <span class="text-[10px] text-slate-500 font-medium">Showing {{ $items->count() }} items</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-100/70 border-b border-slate-200 text-[10px] font-black uppercase text-slate-500 tracking-wider">
                        <th class="py-3 px-4">Item Details & SKU</th>
                        <th class="py-3 px-3">Category</th>
                        <th class="py-3 px-3">Location</th>
                        <th class="py-3 px-3 text-center">In Stock</th>
                        <th class="py-3 px-3 text-center">Reserved Qty</th>
                        <th class="py-3 px-3 text-right">Est. Unit Cost</th>
                        <th class="py-3 px-3 text-right">Total Valuation</th>
                        <th class="py-3 px-4 text-center">Quick Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/80 font-medium text-slate-700">
                    @forelse($items as $item)
                        <tr class="hover:bg-slate-50/80 transition-all {{ $item->stock_quantity <= $item->min_stock_level ? 'bg-rose-50/40' : '' }}">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 text-xs">{{ $item->name }}</div>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="font-mono text-[9px] font-bold text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200">#{{ $item->sku }}</span>
                                    <span class="text-[9px] text-slate-400">Min stock: {{ $item->min_stock_level }} {{ $item->unit }}</span>
                                </div>
                            </td>

                            <td class="py-3.5 px-3">
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-bold border uppercase
                                    @if($item->category === 'Electrical') bg-amber-50 text-amber-800 border-amber-200
                                    @elseif($item->category === 'Plumbing') bg-blue-50 text-blue-800 border-blue-200
                                    @elseif($item->category === 'Security') bg-purple-50 text-purple-800 border-purple-200
                                    @elseif($item->category === 'Cleaning') bg-teal-50 text-teal-800 border-teal-200
                                    @else bg-slate-100 text-slate-700 border-slate-200 @endif">
                                    {{ $item->category }}
                                </span>
                            </td>

                            <td class="py-3.5 px-3 text-slate-600">
                                <span class="text-[11px] truncate block max-w-[130px]" title="{{ $item->storage_location }}">{{ $item->storage_location ?? 'Main Depot' }}</span>
                            </td>

                            <td class="py-3.5 px-3 text-center">
                                <div class="inline-flex flex-col items-center">
                                    <span class="font-mono text-sm font-black {{ $item->stock_quantity <= $item->min_stock_level ? 'text-rose-700 font-extrabold' : 'text-slate-900' }}">
                                        {{ $item->stock_quantity }} {{ $item->unit }}
                                    </span>
                                    @if($item->stock_quantity <= $item->min_stock_level)
                                        <span class="text-[8px] font-black uppercase text-rose-600 bg-rose-100 px-1 rounded border border-rose-200 mt-0.5">Low Stock</span>
                                    @endif
                                </div>
                            </td>

                            <td class="py-3.5 px-3 text-center">
                                <span class="font-mono text-xs font-bold text-amber-800 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                    {{ $item->reserved_quantity }} {{ $item->unit }}
                                </span>
                                @if($item->allocated_budget > 0)
                                    <span class="block text-[8px] text-amber-700 font-bold mt-0.5">{{ format_indian_currency($item->allocated_budget) }}</span>
                                @endif
                            </td>

                            <td class="py-3.5 px-3 text-right font-mono font-bold text-slate-800">
                                {{ format_indian_currency($item->unit_cost) }}
                            </td>

                            <td class="py-3.5 px-3 text-right font-mono font-black text-emerald-800">
                                {{ format_indian_currency($item->stock_quantity * $item->unit_cost) }}
                            </td>

                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button wire:click="openStockMovementModal('{{ $item->id }}', 'Purchase')" 
                                            class="px-2 py-1 rounded bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-[10px] transition-all shadow-2xs flex items-center gap-1 cursor-pointer"
                                            title="Stock IN / Record Purchase">
                                        + IN
                                    </button>

                                    <button wire:click="openStockMovementModal('{{ $item->id }}', 'Issue')" 
                                            class="px-2 py-1 rounded bg-blue-600 hover:bg-blue-500 text-white font-bold text-[10px] transition-all shadow-2xs flex items-center gap-1 cursor-pointer"
                                            title="Stock OUT / Issue for Repair">
                                        - OUT
                                    </button>

                                    <button wire:click="openStockMovementModal('{{ $item->id }}', 'Reserve')" 
                                            class="px-2 py-1 rounded bg-amber-600 hover:bg-amber-500 text-white font-bold text-[10px] transition-all shadow-2xs flex items-center gap-1 cursor-pointer"
                                            title="Reserve Stock for Dedicated Budget">
                                        📌 Reserve
                                    </button>

                                    <button wire:click="openEditInventoryItemModal('{{ $item->id }}')" 
                                            class="px-2 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[10px] transition-all border border-slate-300 cursor-pointer"
                                            title="Edit Item Details">
                                        ✏️ Edit
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-500">
                                <div class="max-w-xs mx-auto text-center space-y-2">
                                    <span class="text-3xl block">📦</span>
                                    <p class="font-bold text-slate-700">No inventory items found</p>
                                    <p class="text-slate-400 text-xs">Get started by creating your first stock item or seeding sample inventory.</p>
                                    <button wire:click="openCreateInventoryItemModal" class="mt-2 px-4 py-2 rounded-xl bg-indigo-600 text-white font-bold text-xs">
                                        + Add First Item
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Inventory Modal (Create / Edit / Stock Movement / Reserve) -->
    @if($showInventoryModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-2xl border border-slate-300 shadow-2xl w-full max-w-md overflow-hidden max-h-[92vh] flex flex-col">
                <!-- Modal Header (Dark High-Contrast Typography) -->
                <div class="px-5 py-3.5 border-b border-slate-300 bg-gradient-to-r from-slate-100 via-slate-50 to-slate-100 text-slate-900 flex items-center justify-between shrink-0">
                    <div>
                        <span class="text-[9px] font-black uppercase tracking-widest text-slate-600 block">Stock Operations & Master</span>
                        <h3 class="text-sm font-black text-slate-900 mt-0.5">
                            @if($inventoryModalMode === 'create') + Create New Inventory Item
                            @elseif($inventoryModalMode === 'edit') ✏️ Edit Inventory Item
                            @elseif($inventoryModalMode === 'movement') 📦 Record Stock Movement / Reservation
                            @endif
                        </h3>
                    </div>
                    <button wire:click="closeInventoryModal" class="text-slate-400 hover:text-slate-900 transition-all cursor-pointer font-extrabold text-lg p-1">
                        ✕
                    </button>
                </div>

                <!-- Modal Body (Scrollable Compact Padding) -->
                <div class="p-4 overflow-y-auto space-y-3 flex-1 text-xs">
                    @if($inventoryModalMode === 'create' || $inventoryModalMode === 'edit')
                        <!-- Item Master Form -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-800 mb-1">Item Name *</label>
                            <input type="text" wire:model="invName" placeholder="e.g. LED Floodlight 50W Outdoor"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-slate-800">
                            @error('invName') <span class="text-rose-600 text-[10px] font-bold block mt-0.5">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-800 mb-1">SKU / Item Code</label>
                                <input type="text" wire:model="invSku" placeholder="Auto-generated if empty"
                                       class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-mono uppercase focus:ring-2 focus:ring-slate-800">
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-800 mb-1">Category *</label>
                                <select wire:model="invCategory" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold focus:ring-2 focus:ring-slate-800">
                                    <option value="Electrical">Electrical</option>
                                    <option value="Plumbing">Plumbing</option>
                                    <option value="Cleaning">Cleaning & Sanitation</option>
                                    <option value="Security">Security & CCTV</option>
                                    <option value="Hardware">Hardware & Construction</option>
                                    <option value="Office">Office & Admin</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-2">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-800 mb-1">Unit *</label>
                                <input type="text" wire:model="invUnit" placeholder="pcs, meters"
                                       class="w-full px-2.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-slate-800">
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-800 mb-1">Min Reorder *</label>
                                <input type="number" wire:model="invMinStock" min="0"
                                       class="w-full px-2.5 py-2 rounded-xl border border-slate-300 text-xs font-mono focus:ring-2 focus:ring-slate-800">
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-800 mb-1">Unit Cost (₹) *</label>
                                <input type="number" step="0.01" wire:model="invUnitCost" min="0"
                                       class="w-full px-2.5 py-2 rounded-xl border border-slate-300 text-xs font-mono focus:ring-2 focus:ring-slate-800">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-800 mb-1">Storage Location</label>
                            <input type="text" wire:model="invStorageLocation" placeholder="e.g. Electric Control Room, Shelf B-4"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-slate-800">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-800 mb-1">Remarks / Technical Specifications</label>
                            <textarea wire:model="invRemarks" rows="2" placeholder="e.g. 2 years warranty, IP65 waterproof rating, purchased from Siemens vendor"
                                      class="w-full px-3 py-1.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-slate-800 resize-none"></textarea>
                        </div>

                    @elseif($inventoryModalMode === 'movement')
                        <!-- Stock Movement / Reservation Form -->
                        <div class="bg-indigo-50/60 p-3 rounded-xl border border-indigo-100 flex items-center justify-between">
                            <div>
                                <span class="text-[10px] font-bold text-indigo-700 uppercase tracking-wider block">Target Item</span>
                                <strong class="text-sm font-black text-slate-900 block">
                                    {{ \App\Models\InventoryItem::find($editingItemId)?->name }}
                                </strong>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Current Stock</span>
                                <span class="font-mono text-sm font-black text-slate-900">
                                    {{ \App\Models\InventoryItem::find($editingItemId)?->stock_quantity }} {{ \App\Models\InventoryItem::find($editingItemId)?->unit }}
                                </span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Operation Type *</label>
                            <select wire:model.live="transType" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold focus:ring-2 focus:ring-indigo-500">
                                <option value="Purchase">+ Stock IN (Record Purchase)</option>
                                <option value="Issue">- Stock OUT (Issue for Repair/Maintenance)</option>
                                <option value="Reserve">📌 Reserve Item for Project (Dedicated Budget)</option>
                                <option value="Adjustment">⚙️ Stock Audit Adjustment</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Quantity *</label>
                                <input type="number" wire:model="transQuantity" min="1"
                                       class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-mono font-bold focus:ring-2 focus:ring-indigo-500">
                                @error('transQuantity') <span class="text-rose-600 text-[10px] font-bold block mt-0.5">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Unit Price (₹) *</label>
                                <input type="number" step="0.01" wire:model="transUnitPrice" min="0"
                                       class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-mono font-bold focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                        @if($transType === 'Reserve' || $transType === 'Issue')
                            <div class="space-y-3 pt-2 border-t border-slate-200">
                                <span class="text-[10px] font-black uppercase text-indigo-700 tracking-wider block">Link Project / Proposal / Dedicated Budget</span>
                                
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-600 mb-1">Development Proposal</label>
                                        <select wire:model="transProposalId" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs">
                                            <option value="">-- Optional Proposal --</option>
                                            @foreach($proposals as $prop)
                                                <option value="{{ $prop->id }}">{{ $prop->title }} ({{ format_indian_currency($prop->budget) }})</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-600 mb-1">Active Project</label>
                                        <select wire:model="transProjectId" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs">
                                            <option value="">-- Optional Project --</option>
                                            @foreach($projects as $proj)
                                                <option value="{{ $proj->id }}">{{ $proj->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Remarks / Note</label>
                            <input type="text" wire:model="transRemarks" placeholder="e.g. Disbursed for Block A Lift repair"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500">
                        </div>
                    @endif
                </div>

                <!-- Modal Footer -->
                <div class="px-5 py-3 border-t border-slate-300 bg-slate-100/70 flex items-center justify-end gap-2 shrink-0">
                    <button wire:click="closeInventoryModal" type="button" 
                            class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 font-extrabold text-xs hover:bg-slate-200 transition-all cursor-pointer">
                        Cancel
                    </button>

                    @if($inventoryModalMode === 'create' || $inventoryModalMode === 'edit')
                        <button wire:click="saveInventoryItem" type="button" 
                                class="px-5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-black font-extrabold text-xs shadow-md transition-all cursor-pointer border border-slate-800">
                            Save Inventory Item
                        </button>
                    @elseif($inventoryModalMode === 'movement')
                        <button wire:click="saveStockMovement" type="button" 
                                class="px-5 py-2 rounded-xl bg-emerald-800 hover:bg-emerald-700 text-black font-extrabold text-xs shadow-md transition-all cursor-pointer">
                            Confirm Movement
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
