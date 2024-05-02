@extends($layout)

@section('content')

<x-global::pageheader :icon="'fa fa-table'">
    <h1>Import To-Dos</h1>
</x-global::pageheader>
<link rel="stylesheet" href="<?php echo $tpl->get('importStyling') ?>" />
<script type="module" src="<?php echo $tpl->get('importScript') ?>"></script>

<div class="maincontent">
    <?php echo $tpl->displayNotification(); ?>
    <?php
    $mappings = $tpl->get('mappings');
    $supportedFields = $tpl->get('supportedFields');
    ?>
    <div class="maincontentinner">

        <ol class="steps">
            <li class="step is-complete" data-step="1">
                Upload file
            </li>
            <li class="step is-complete" data-step="2">
                Settings
            </li>
            <li class="step is-complete" data-step="3">
                Map fields
            </li>
            <li class="step is-active" data-step="4">
                Review
            </li>
        </ol>
        <form enctype="multipart/form-data" action="" method="post">

        <?php
        echo "<h2>Warnings:</h2>";
        foreach($validationWarnings as $validationWarning) {
            echo "<p>".$validationWarning."</p>";
        }
        echo "</br></br>";
        echo "<h2>Errors:</h2>";
        foreach($validationErrors as $errorGroupName => $errorGroup) {
            echo "<h3>".$errorGroupName." (".count($errorGroup)." errors)</h3>";
            foreach($errorGroup as $errorSubject => $errorSpec) {
                echo "<p>".$errorSpec." <b>".$errorSubject."</b></p>";

            }
        }
        echo "</br></br>";
        foreach($tpl->get('dataToValidate') as $count => $dataToValidate) {
            echo "<h3>Ticket ".$count."</h3>";
            foreach($dataToValidate as $field => $datumToValidate) {
                $field = str_replace(' ', '_', $field);
                echo "<p data-hest='".$field."'>".$supportedFields[$mappings[$field]]["name"]." -> ".$datumToValidate."</p>";

            }
            echo "<br />";
        }
        ?>

            <div class="form-group">
                <label for="dataValidated"><input id="dataValidated" type="checkbox" name="dataValidated" /> Jeg er sikker på at data er korrekt mappet, og vil gerne importere det i Leantime.</label>
            </div>
            <br />

            <a href="/EstimateImport/importMapping" class="btn btn-inverse">Tilbage</a>
            <input type="submit" value="Videre!" id="validateMappingSubmit" disabled type="submit" class="btn btn-primary" />
        </form>

    </div>
</div>
@endsection
