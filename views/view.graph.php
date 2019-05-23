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
            <div class="chart_title">Percentage of tests passed and failed</div>
            <canvas id="chart" style="width: 100%;" height="250"></canvas>
        </div>

        <div class="canvas_container">
            <div class="chart_title">Statistics about test failures</div>
            <canvas id="chart_precise" style="width: 100%;" height="250"></canvas>
            <div class="chart_legend">
                <p><span>Assertion error</span>: Actual result differs from expected.</p>
                <p><span>File not found</span>: Test didn't find the file it was looking for.</p>
                <p><span>Timeout</span>: Object not found after waiting for it to be visible.</p>
                <p><span>Object not found</span>: Selector not valid.</p>
            </div>
        </div>
    </div>
</div>
<script>
    window.onload = function() {

        const opacity = 0.7;

        //first graph
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
                        backgroundColor: 'rgba(3, 86, 3, '+opacity+')',
                        fill: 'origin'
                    },
                    {
                        label: '% failed',
                        data: failed_percent,
                        backgroundColor: 'rgba(178, 44, 44, '+opacity+')',
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

        //second graph
        const precise_data = {{JSON_FAILURES_DATA}};
        const p_labels = Array.from(precise_data, x => x.custom_start_date);
        const p_value_expected = Array.from(precise_data, x => x.value_expected);
        const p_file_not_found = Array.from(precise_data, x => x.file_not_found);
        const p_not_visible_after_timeout = Array.from(precise_data, x => x.not_visible_after_timeout);
        const p_wrong_locator = Array.from(precise_data, x => x.wrong_locator);

        var p_canvas = document.getElementById('chart_precise');
        var p_ctx = p_canvas.getContext('2d');
        var p_testChart = new Chart(p_ctx, {
            type: 'bar',
            data: {
                labels: p_labels,
                datasets: [
                    {
                        label: 'Assertion error',
                        data: p_value_expected,
                        backgroundColor: 'rgba(68, 111, 142, '+opacity+')',
                        fill: 'origin'
                    },
                    {
                        label: 'File not found',
                        data: p_file_not_found,
                        backgroundColor: 'rgba(68, 142, 68, '+opacity+')',
                        fill: '-1'
                    },
                    {
                        label: 'Timeout',
                        data: p_not_visible_after_timeout,
                        backgroundColor: 'rgba(255, 206, 86, '+opacity+')',
                        fill: '-2'
                    },
                    {
                        label: 'Object not found',
                        data: p_wrong_locator,
                        backgroundColor: 'rgba(153, 102, 255, '+opacity+')',
                        fill: '-3'
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
                            min: 0
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
        };
    }
</script>