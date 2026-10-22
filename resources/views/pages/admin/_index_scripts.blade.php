<script>
    $( function() {

        ///////////////////////////////////////////////////////////
        // tooltips
        $("[data-toggle=tooltip]").tooltip();

        ///////////////////////////////////////////////////////////
        // custom scroll
        if (!('ontouchstart' in document.documentElement) && jQuery().jScrollPane) {
            $('.custom-scroll').each(function() {
                $(this).jScrollPane({
                    contentWidth: '100%',
                    autoReinitialise: true,
                    autoReinitialiseDelay: 100
                });
                var api = $(this).data('jsp'),
                        throttleTimeout;
                $(window).bind('resize', function() {
                    if (!throttleTimeout) {
                        throttleTimeout = setTimeout(function() {
                            api.reinitialise();
                            throttleTimeout = null;
                        }, 50);
                    }
                });
            });
        }

    } );

    ///////////////////////////////////////////////////////////
    // Overview chart (Sales / Purchases / Expenses per month)
    (function () {
        const canvas = document.getElementById('overviewChart');
        if (!canvas || typeof Chart === 'undefined') return;

        new Chart(canvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
                datasets: [
                    {
                        label: 'Sales',
                        data: @json($monthlySales),
                        borderColor: '#4f46e5',
                        backgroundColor: 'rgba(79,70,229,.08)',
                        lineTension: 0.4,
                        fill: true,
                        pointRadius: 0,
                        borderWidth: 2,
                    },
                    {
                        label: 'Purchases',
                        data: @json($monthlyPurchases),
                        borderColor: '#16a34a',
                        backgroundColor: 'rgba(22,163,74,.08)',
                        lineTension: 0.4,
                        fill: true,
                        pointRadius: 0,
                        borderWidth: 2,
                    },
                    {
                        label: 'Expenses',
                        data: @json($monthlyExpenses),
                        borderColor: '#d97706',
                        backgroundColor: 'rgba(217,119,6,.08)',
                        lineTension: 0.4,
                        fill: true,
                        pointRadius: 0,
                        borderWidth: 2,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false },
                scales: {
                    yAxes: [{ ticks: { beginAtZero: true }, gridLines: { color: '#f1f3f8' } }],
                    xAxes: [{ gridLines: { display: false } }],
                },
            },
        });
    })();
</script>
