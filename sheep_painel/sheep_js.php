       
  <script src="assets/js/app.min.js"></script>

  
  <!-- JS Libraies -->
  <!-- <script src="assets/bundles/apexcharts/apexcharts.min.js"></script> -->
  <script src="assets/js/page/index.js"></script>
  <script src="assets/bundles/dropzonejs/min/dropzone.min.js"></script>
  <!-- Page Specific JS File -->
  <script src="assets/js/page/multiple-upload.js"></script>
  <script src="assets/bundles/summernote/summernote-bs4.js"></script>
  <script src="assets/bundles/cleave-js/dist/cleave.min.js"></script>
  <script src="assets/bundles/cleave-js/dist/addons/cleave-phone.us.js"></script>
  <script src="assets/bundles/jquery-pwstrength/jquery.pwstrength.min.js"></script>
  <script src="assets/bundles/bootstrap-daterangepicker/daterangepicker.js"></script>
  <script src="assets/bundles/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min.js"></script>
  <script src="assets/bundles/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
  <script src="assets/bundles/bootstrap-tagsinput/dist/bootstrap-tagsinput.min.js"></script>
  <script src="assets/bundles/upload-preview/assets/js/jquery.uploadPreview.min.js"></script>
  <script src="assets/bundles/datatables/datatables.min.js"></script>
  <script src="assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js"></script>
  <script src="assets/bundles/select2/dist/js/select2.full.min.js"></script>



  
  <!-- Page Specific JS File -->
  <script src="assets/js/page/toastr.js"></script>
  <!-- Page Specific JS File -->
  <script src="assets/js/page/datatables.js"></script>
  <script src="assets/bundles/lightgallery/dist/js/lightgallery-all.js"></script>
  <!-- Page Specific JS File -->
  <script src="assets/js/page/light-gallery.js"></script>
  <!-- Template JS File -->
  <script src="assets/js/scripts.js"></script>
  <!-- Custom JS File -->
  <script src="assets/js/jmask.js?v=20260602"></script>
  <script src="assets/js/custom.js?v=20260602"></script>

  <script>
    (function () {
      const PRINT_STORAGE_KEY = 'mondini_auto_printed_orders';
      const PRINT_TTL_MS = 6 * 60 * 60 * 1000;

      function getPrintedMap() {
        try {
          const parsed = JSON.parse(localStorage.getItem(PRINT_STORAGE_KEY) || '{}');
          const now = Date.now();
          Object.keys(parsed).forEach(id => {
            if (!parsed[id] || now - parsed[id] > PRINT_TTL_MS) {
              delete parsed[id];
            }
          });
          localStorage.setItem(PRINT_STORAGE_KEY, JSON.stringify(parsed));
          return parsed;
        } catch (e) {
          return {};
        }
      }

      function markPrinted(id) {
        const printed = getPrintedMap();
        printed[id] = Date.now();
        localStorage.setItem(PRINT_STORAGE_KEY, JSON.stringify(printed));
      }

      window.imprimirPedidoAutomatico = function (id) {
        if (!id) return;

        const printed = getPrintedMap();
        if (printed[id]) return;
        markPrinted(id);

        const frame = document.createElement('iframe');
        frame.setAttribute('aria-hidden', 'true');
        frame.src = 'imprimir.php?id=' + encodeURIComponent(id) + '&auto=1&t=' + Date.now();
        frame.style.position = 'fixed';
        frame.style.right = '0';
        frame.style.bottom = '0';
        frame.style.width = '1px';
        frame.style.height = '1px';
        frame.style.border = '0';
        frame.style.opacity = '0.01';
        frame.style.pointerEvents = 'none';
        frame.style.zIndex = '-1';

        frame.onload = function () {
          setTimeout(function () {
            try {
              frame.contentWindow.focus();
              frame.contentWindow.print();
            } catch (e) {
              console.error('Falha ao imprimir pedido #' + id, e);
            }
          }, 350);
        };

        document.body.appendChild(frame);
        setTimeout(function () {
          if (frame.parentNode) frame.parentNode.removeChild(frame);
        }, 30000);
      };

      function monitorarPedidosForaDoDashboard() {
        if (document.getElementById('pdvDashboardView')) return;

        fetch('sheep-filtros/get_status_pdv.php', { cache: 'no-store' })
          .then(res => res.json())
          .then(data => {
            if (!data || !data.sucesso || !Array.isArray(data.auto_print_ids)) return;
            data.auto_print_ids.forEach(id => window.imprimirPedidoAutomatico(id));
          })
          .catch(err => console.error('Erro no monitor global de pedidos:', err));
      }

      monitorarPedidosForaDoDashboard();
      setInterval(monitorarPedidosForaDoDashboard, 7000);
    })();
  </script>

  <!-- JS Libraies -->
  <!-- <script src="assets/bundles/amcharts4/core.js"></script>
  <script src="assets/bundles/amcharts4/charts.js"></script>
  <script src="assets/bundles/amcharts4/animated.js"></script>
  <script src="assets/bundles/amcharts4/worldLow.js"></script>
  <script src="assets/bundles/amcharts4/maps.js"></script> -->
  <!-- Page Specific JS File -->
  <!-- <script src="assets/js/page/chart-amchart.js"></script> -->
  <script src="assets/bundles/jquery-selectric/jquery.selectric.min.js"></script>
  <script src="assets/js/page/create-post.js"></script>   

 
  

  <?php 
  include_once("sheep_flash_msg.php");

  ?>



