document.addEventListener("DOMContentLoaded", function() {
  function initDataTable() {
      let table = $('#dataTable');

      if ($.fn.DataTable.isDataTable(table)) {
          table.DataTable().destroy(); // Hancurkan DataTables sebelum refresh
      }

      table.DataTable({
          "responsive": true,
          "autoWidth": false,
          "paging": true,
          "searching": true,
          "info": true,
          "ordering": true
      });
  }

  initDataTable(); // Inisialisasi pertama kali

  // Hook untuk menangani perubahan Livewire
  Livewire.hook('message.processed', (message, component) => {
      setTimeout(() => {
          initDataTable(); // Inisialisasi ulang setelah render ulang
      }, 300);
  });
});
