/* Retain the form's native select as the submitted source of truth. */
class BranchManagerPicker {
    constructor(select) {
        this.select = select;
        this.value = select.value;
        this.managers = [];
        this.root = document.createElement('div');
        this.root.className = 'branch-manager-picker';
        this.button = document.createElement('button');
        this.button.type = 'button';
        this.button.id = select.id + '_picker';
        this.button.className = 'branch-manager-trigger';
        this.button.setAttribute('aria-haspopup', 'listbox');
        this.button.setAttribute('aria-expanded', 'false');
        this.list = document.createElement('div');
        this.list.id = select.id + '_options';
        this.list.className = 'branch-manager-options';
        this.list.setAttribute('role', 'listbox');
        this.list.setAttribute('aria-label', 'Branch manager');
        this.list.hidden = true;
        this.button.setAttribute('aria-controls', this.list.id);
        select.after(this.root);
        this.root.append(this.button, this.list);
        select.hidden = true;
        const label = document.querySelector('label[for="' + select.id + '"]');
        if (label) label.htmlFor = this.button.id;
        this.button.addEventListener('click', () => this.toggle());
        this.button.addEventListener('keydown', event => {
            if (['ArrowDown', 'ArrowUp'].includes(event.key)) { event.preventDefault(); this.open(event.key === 'ArrowUp'); }
        });
        this.root.addEventListener('keydown', event => {
            if (event.key === 'Escape' && !this.list.hidden) { event.preventDefault(); event.stopPropagation(); this.close(true); }
            if (this.list.hidden || !this.list.contains(event.target)) return;
            const options = [...this.list.children];
            let index = options.indexOf(document.activeElement);
            if (event.key === 'ArrowDown') index = (index + 1) % options.length;
            else if (event.key === 'ArrowUp') index = (index + options.length - 1) % options.length;
            else if (event.key === 'Home') index = 0;
            else if (event.key === 'End') index = options.length - 1;
            else return;
            event.preventDefault(); options[index].focus();
        });
        document.addEventListener('click', event => { if (!this.root.contains(event.target)) this.close(); });
        this.root.addEventListener('focusout', () => setTimeout(() => { if (!this.root.contains(document.activeElement)) this.close(); }, 0));
        select.closest('.modal')?.addEventListener('hidden.bs.modal', () => this.close());
        this.render();
    }
    row(manager) {
        const copy = document.createElement('span');
        copy.className = 'branch-manager-copy';
        const name = document.createElement('span'); name.textContent = manager.name; copy.append(name);
        if (manager.assigned_branch_names) { const detail = document.createElement('small'); detail.textContent = manager.assigned_branch_names; copy.append(detail); }
        const badge = document.createElement('span');
        badge.className = 'branch-manager-badge ' + (manager.assigned_branch_names ? 'is-assigned' : 'is-available');
        badge.textContent = manager.assigned_branch_names ? 'Assigned' : 'Not assigned';
        return [copy, badge];
    }
    setManagers(managers) {
        this.managers = managers;
        this.select.replaceChildren(new Option('Unassigned', ''));
        managers.forEach(manager => this.select.add(new Option(manager.name, String(manager.id))));
        this.setValue(this.value);
    }
    setValue(value) { this.value = String(value || ''); this.select.value = this.value; this.render(); }
    render() {
        const selected = this.managers.find(manager => String(manager.id) === this.value);
        this.button.replaceChildren();
        if (selected) this.button.append(...this.row(selected));
        else { const text = document.createElement('span'); text.textContent = 'Unassigned'; this.button.append(text); }
        this.list.replaceChildren();
        [{id: '', name: 'Unassigned'}, ...this.managers].forEach(manager => {
            const option = document.createElement('button');
            option.type = 'button'; option.className = 'branch-manager-option'; option.setAttribute('role', 'option');
            option.setAttribute('aria-selected', String(String(manager.id) === this.value));
            option.tabIndex = -1;
            if (manager.id === '') option.textContent = manager.name;
            else option.append(...this.row(manager));
            option.addEventListener('click', () => { this.setValue(manager.id); this.select.dispatchEvent(new Event('change', {bubbles: true})); this.close(true); });
            this.list.append(option);
        });
    }
    toggle() { this.list.hidden ? this.open() : this.close(); }
    open(last = false) {
        this.list.hidden = false; this.button.setAttribute('aria-expanded', 'true');
        const selected = this.list.querySelector('[aria-selected="true"]');
        (last ? this.list.lastElementChild : selected || this.list.firstElementChild).focus();
    }
    close(focus = false) { this.list.hidden = true; this.button.setAttribute('aria-expanded', 'false'); if (focus) this.button.focus(); }
}
