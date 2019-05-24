<div class="navbar">
    <div class="navbar_container">
        <div class="links">
            {{LINKS}}
        </div>
        <div class="title">
            <h2>{{TITLE}}</h2>
        </div>
        <div class="recap">
            {{RECAP}}
        </div>
    </div>
</div>
<div class="container">

    <div class="details">
        <div class="options">
            <div class="blocks_container">
                <div class="block">
                    Start Date : {{START_DATE}}
                </div>
                <div class="block">
                    End Date : {{END_DATE}}
                </div>
            </div>
        </div>
        <div id="left_navigation">
            <div class="navigation_block">
                <h4>Options</h4>
                <div class="buttons">
                    <div class="button">
                        <button id="toggle_failed" data-state="shown">Hide Passed Tests</button>
                    </div>
                </div>
            </div>
            <hr>
            <div class="navigation_block">
                <h4>Navigation</h4>
                <div class="navigation">
                    {{NAVIGATION}}
                </div>
            </div>
            <hr>
            <div class="navigation_block">
                <div class="additional_infos">
                    <h4>Additional Info</h4>
                    <div class="info">
                        <span><i class="material-icons">bug_report</i> Invalid Session ID bugs: </span> {{INVALID_SESSION_ID_COUNT}}
                    </div>
                </div>
            </div>
        </div>
        <div id="content">
            {{CONTENT}}
        </div>

    </div>
</div>
<script>
        $(document).ready(function() {
            $('.test_title').click(function() {
                let id = $(this).attr('id');
                $('#stack_'+id).toggle();
            });

            $('#toggle_failed').click(function() {
                let state = $(this).data('state');

                if (state === 'shown') {
                    $('section.suite.hasPassed:not(.hasFailed)').hide();
                    $('section.test_component.passed').hide();
                    $(this).html('Show Passed Tests');
                    $(this).data('state', 'hidden');
                } else {
                    $('section.suite.hasPassed:not(.hasFailed)').show();
                    $('section.test_component.passed').show();
                    $(this).html('Hide Passed Tests');
                    $(this).data('state', 'shown');
                }
            });
        });
</script>