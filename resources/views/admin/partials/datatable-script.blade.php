<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.min.js"></script>

<script src="https://cdn.datatables.net/buttons/3.1.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.colVis.min.js"></script>

<script>
$(document).ready(function () {
    $('{{ $tableId }}').DataTable({
        language: { url: 'https://cdn.datatables.net/plug-ins/2.0.8/i18n/ar.json' },
        responsive: true,
        pageLength: 10,
        dom: "<'row'<'col-sm-4'l><'col-sm-4 text-center'B><'col-sm-4'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [
            { extend: 'excel', className: 'btn btn-success btn-sm', text: '📊 Excel' },
            { extend: 'pdf', className: 'btn btn-danger btn-sm', text: '📄 PDF' },
            { extend: 'print', className: 'btn btn-info btn-sm', text: '🖨 طباعة' },
            { extend: 'colvis', className: 'btn btn-secondary btn-sm', text: '📌 عرض الأعمدة' }
        ]
    });
});
</script>
