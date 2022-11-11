<!doctype html>
<html lang="cs">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chuck it</title>
    <meta name='robots' content='noindex, nofollow' />
    <link rel='stylesheet' href='dist/css/app.css' media='all' />
</head>
<body>
    <div class="container d-flex flex-column flex-lg-row my-5 justify-content-between">
        <button type="button" data-task="ch_random" class="btn btn-primary">Random joke</button>
        <button type="button" data-task="ch_names" class="btn btn-secondary">Funny names</button>
        <button type="button" data-task="ch_numbers" class="btn btn-danger">Strange records</button>
        <button type="button" data-task="ch_date" class="btn btn-success">Recent and upcoming</button>
        <button type="button" data-task="ch_check" class="btn btn-warning">Make no mistake</button>
    </div>
    <div class="results my-4">
        <div class="container" data-results></div>
    </div>
    <div id="ch_overlay">
        <div class="container h-100 d-flex justify-content-center">
            <div class="row align-items-center">
                <div class="ch_spinner text-primary me-2 align-middle"></div>
            </div>
        </div>
    </div>
    <script src="dist/js/app.js"></script>
</body>
</html>