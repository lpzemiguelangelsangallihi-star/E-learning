@props(['eventos' => []])

<div
    x-data="{
        eventos: @js($eventos),
        meses: ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
        hoy: new Date(),
        y: new Date().getFullYear(),
        m: new Date().getMonth(),
        sel: null,
        clave(y, m, d) {
            return y + '-' + String(m + 1).padStart(2, '0') + '-' + String(d).padStart(2, '0');
        },
        init() { this.sel = this.clave(this.hoy.getFullYear(), this.hoy.getMonth(), this.hoy.getDate()); },
        get hoyClave() { return this.clave(this.hoy.getFullYear(), this.hoy.getMonth(), this.hoy.getDate()); },
        get celdas() {
            const primero = new Date(this.y, this.m, 1);
            const vacios = (primero.getDay() + 6) % 7;
            const dias = new Date(this.y, this.m + 1, 0).getDate();
            const out = [];
            for (let i = 0; i < vacios; i++) { out.push(null); }
            for (let d = 1; d <= dias; d++) {
                const k = this.clave(this.y, this.m, d);
                const lista = this.eventos.filter(e => e.fecha === k);
                out.push({ d, k, n: lista.length, vencida: lista.some(e => e.vencida) });
            }
            return out;
        },
        get delDia() { return this.eventos.filter(e => e.fecha === this.sel); },
        mover(n) {
            const f = new Date(this.y, this.m + n, 1);
            this.y = f.getFullYear();
            this.m = f.getMonth();
        },
        irHoy() {
            this.y = this.hoy.getFullYear();
            this.m = this.hoy.getMonth();
            this.sel = this.hoyClave;
        },
        get etiquetaSel() {
            if (!this.sel) { return ''; }
            const [a, b, c] = this.sel.split('-').map(Number);
            return c + ' de ' + this.meses[b - 1].toLowerCase();
        }
    }"
>
<x-ui.card>
    <div class="mb-4 flex items-center justify-between gap-3">
        <div>
            <h2 class="text-base font-bold text-slate-800">Calendario</h2>
            <p class="mt-1 text-sm text-slate-500">Fechas de entrega de tus tareas.</p>
        </div>

        <div class="flex items-center gap-1">
            <button type="button" @click="mover(-1)" aria-label="Mes anterior"
                class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100">
                <i class="fa-solid fa-chevron-left text-xs" aria-hidden="true"></i>
            </button>

            <span class="min-w-[8.5rem] text-center text-sm font-semibold text-slate-800"
                x-text="meses[m] + ' ' + y"></span>

            <button type="button" @click="mover(1)" aria-label="Mes siguiente"
                class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100">
                <i class="fa-solid fa-chevron-right text-xs" aria-hidden="true"></i>
            </button>

            <button type="button" @click="irHoy()"
                class="ml-1 rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                Hoy
            </button>
        </div>
    </div>

    <div class="grid grid-cols-7 gap-1 text-center text-xs font-semibold uppercase tracking-wide text-slate-400">
        <template x-for="n in ['L','M','X','J','V','S','D']" :key="n">
            <div class="py-1" x-text="n"></div>
        </template>
    </div>

    <div class="mt-1 grid grid-cols-7 gap-1">
        <template x-for="(c, i) in celdas" :key="i">
            <div>
                <template x-if="c === null">
                    <div class="h-10"></div>
                </template>

                <template x-if="c !== null">
                    <button type="button" @click="sel = c.k"
                        class="relative flex h-10 w-full items-center justify-center rounded-lg text-sm transition"
                        :class="[
                            sel === c.k ? 'bg-[#2563EB] font-semibold text-white' : 'text-slate-700 hover:bg-slate-100',
                            c.k === hoyClave && sel !== c.k ? 'ring-1 ring-[#2563EB]' : ''
                        ]">
                        <span x-text="c.d"></span>
                        <span x-show="c.n > 0"
                            class="absolute bottom-1 h-1.5 w-1.5 rounded-full"
                            :class="sel === c.k ? 'bg-white' : (c.vencida ? 'bg-red-500' : 'bg-amber-500')"></span>
                    </button>
                </template>
            </div>
        </template>
    </div>

    <div class="mt-4 border-t border-slate-100 pt-4">
        <p class="mb-2 text-sm font-semibold text-slate-800">
            Entregas del <span x-text="etiquetaSel"></span>
        </p>

        <template x-if="delDia.length === 0">
            <p class="text-sm text-slate-500">No tienes entregas ese día.</p>
        </template>

        <div class="space-y-2">
            <template x-for="e in delDia" :key="e.url">
                <a :href="e.url"
                    class="flex items-center justify-between gap-3 rounded-lg border border-slate-200 p-3 transition hover:border-[#2563EB]">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-slate-800" x-text="e.titulo"></p>
                        <p class="truncate text-xs text-slate-500" x-text="e.curso"></p>
                    </div>
                    <span class="shrink-0 text-xs font-medium"
                        :class="e.vencida ? 'text-red-600' : 'text-slate-500'"
                        x-text="(e.vencida ? 'Vencida · ' : '') + e.hora"></span>
                </a>
            </template>
        </div>
    </div>
</x-ui.card>
</div>
