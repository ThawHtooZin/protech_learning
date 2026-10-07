/**
 * Grant-access picker: add from search list + manage via searchable table.
 */
export function registerCourseAccessPicker(Alpine) {
    Alpine.data('courseAccessPicker', (options = [], selectedKeys = []) => ({
        options,
        selected: [...selectedKeys],
        addQuery: '',
        tableQuery: '',
        highlight: 0,

        get selectedItems() {
            return this.selected
                .map((key) => this.options.find((o) => o.key === key))
                .filter(Boolean);
        },

        get tableRows() {
            const q = this.tableQuery.trim().toLowerCase();
            return this.selectedItems.filter((o) => {
                if (!q) return true;
                return (
                    String(o.label).toLowerCase().includes(q) ||
                    String(o.meta || '').toLowerCase().includes(q) ||
                    String(o.type).toLowerCase().includes(q)
                );
            });
        },

        get addCandidates() {
            const q = this.addQuery.trim().toLowerCase();
            return this.options
                .filter((o) => !this.selected.includes(o.key))
                .filter((o) => {
                    if (!q) return true;
                    return (
                        String(o.label).toLowerCase().includes(q) ||
                        String(o.meta || '').toLowerCase().includes(q) ||
                        String(o.type).toLowerCase().includes(q)
                    );
                })
                .slice(0, 50);
        },

        add(key) {
            if (!this.selected.includes(key)) {
                this.selected.push(key);
            }
            this.addQuery = '';
            this.highlight = 0;
            this.$nextTick(() => this.$refs.addSearch?.focus());
        },

        remove(key) {
            this.selected = this.selected.filter((k) => k !== key);
        },

        move(delta) {
            if (!this.addCandidates.length) return;
            this.highlight = (this.highlight + delta + this.addCandidates.length) % this.addCandidates.length;
        },

        pickHighlighted() {
            const item = this.addCandidates[this.highlight];
            if (item) this.add(item.key);
        },

        syncHidden() {},
    }));
}
