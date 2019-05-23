<div class="navbar">
    <div class="navbar_container">
        <div class="links">
            {{LINKS}}
        </div>
        <div class="title">
            <h2>{{TITLE}}</h2>
        </div>
    </div>
</div>
<div class="container">
    <div class="details">
        <div class="options">
            <div class="blocks_container">
                <form action="" method="GET" id="graphform">
                    <div class="form_block">
                        <div class="form_container">
                            <label for="start_date">
                                Start date
                                <input type="date" name="start_date" id="start_date" value="{{START_DATE}}" max="{{CURRENT_DATE}}"/>
                            </label>
                        </div>
                        <div class="form_container">
                            <label for="end_date">
                                End date
                                <input type="date" name="end_date" id="end_date" value="{{END_DATE}}" max="{{CURRENT_DATE}}"/>
                            </label>
                        </div>
                    </div><div class="form_block">
                        <label for="version">
                            Version
                            <select name="version" id="version">
                                {{SELECT_VERSION}}
                            </select>
                        </label>
                    </div><div class="form_block">
                        <label for="Campaign">
                            Campaign
                            <select name="campaign" id="campaign">
                                <option value="">All</option>
                                {{SELECT_CAMPAIGN}}
                            </select>
                        </label>
                    </div><div class="form_block">
                        <button>Filter</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="canvas_container">
            <canvas id="chart" style="width: 100%;" height="250"></canvas>
        </div>
    </div>
</div>
<script>
    window.onload = function() {
        const data = {{JSON_DATA}};
        const labels = Array.from(data, x => x.custom_start_date);
        const passed_percent = Array.from(data, x => Math.round((parseFloat(x.totalPasses)*10000 / (parseFloat(x.totalPasses) + parseFloat(x.totalSkipped) + parseFloat(x.totalFailures))))/100 );
        const failed_percent = Array.from(data, x => Math.round((parseFloat(x.totalFailures)*10000 / (parseFloat(x.totalPasses) + parseFloat(x.totalSkipped) + parseFloat(x.totalFailures))))/100 );
        const minValue_percent = Math.min.apply(null, passed_percent) - 20;

        var canvas = document.getElementById('chart');
        var ctx = canvas.getContext('2d');
        var testChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: '% passed',
                        data: passed_percent,
                        backgroundColor: 'rgba(75, 192, 192, 0.6)',
                        fill: 'origin'
                    },
                    {
                        label: '% failed',
                        data: failed_percent,
                        backgroundColor: 'rgba(255, 99, 132, 0.6)',
                        fill: '-1'
                    }]
            },
            options: {
                scales: {
                    xAxes: [{
                        stacked: true
                    }],
                    yAxes: [{
                        stacked: true,
                        ticks: {
                            min: minValue_percent
                        }
                    }]
                },
                legend: {
                    display: true,
                    labels: {
                        fontColor: 'rgb(255, 99, 132)'
                    }
                }
            }
        });

        canvas.onclick = function(e) {
            var slice = testChart.getElementAtEvent(e);
            if (!slice.length) return; // return if not clicked on slice
            var label = slice[0]._model.label;
            let item = data.find(function(element) {
                return element.custom_start_date == label;
            });
            window.open('{{BASEURL}}display.php?id='+item.id, '_blank');
        }
    }
</script>