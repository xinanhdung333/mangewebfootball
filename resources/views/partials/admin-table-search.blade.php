<div class="admin-table-search mb-3" data-table-id="{{ $tableId }}">
    <div class="input-group" style="max-width: 430px;">
        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
        <input type="search" class="form-control" placeholder="{{ $placeholder ?? 'Tìm kiếm trong danh sách...' }}" aria-label="Tìm kiếm">
    </div>
</div>
<script>
(() => {
    const search = document.currentScript.previousElementSibling;
    const input = search.querySelector('input');
    const table = document.getElementById(search.dataset.tableId);
    if (!table) return;
    input.addEventListener('input', () => {
        const term = input.value.trim().toLocaleLowerCase('vi');
        table.querySelectorAll('tbody > tr').forEach(row => {
            row.hidden = term !== '' && !row.textContent.toLocaleLowerCase('vi').includes(term);
        });
    });
})();
</script>
