@extends($layout)

@section('content')

<x-global::pageheader :icon="'fa fa-table'">
    <h1>Import To-Dos</h1>
</x-global::pageheader>
<link rel="stylesheet"  href="<?php echo $tpl->get('importStyling') ?>" />
<script type="module" src="<?php echo $tpl->get('importScript') ?>"></script>

<div class="maincontent">
    <?php echo $tpl->displayNotification(); ?>
    <div class="maincontentinner">

    <ol class="steps">
  <li class="step is-complete" data-step="1">
    Upload file
  </li>
  <li class="step is-active" data-step="2">
    Settings
  </li>
  <li class="step" data-step="3">
    Map fields
  </li>
  <li class="step" data-step="4">
    Review
  </li>
</ol>
        <form action="" method="post">
            <div class="form-group">
                <label for="projectId">Project</label>
                <select name="projectId">
                    <option>Import to Project</option>
                    <?php
                        foreach ($tpl->get('projectData') as $projectDatum) {
                            echo "<option " . ($projectDatum['id'] === $tpl->get('currentProject') ? 'selected' : '') . "  value='" . $projectDatum['id'] . "'>" . $projectDatum['name'] . "</option>";
                        }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="dateFormat">Date format</label>
                <input name="dateFormat" type="text" required placeholder="Date format" value="d/m/Y" />
                <p>ex. (d/m/Y ~ 03/05/2024)</p>
                <p>Please specify the format that dates are stored in the CSV file. <a href="https://www.w3schools.com/php/func_date_date.asp" target="_blank">Syntax examples</a></p>
            </div>

            <br />

            <a href="/EstimateImport/import" class="btn btn-inverse">Tilbage</a>
            <input type="submit" value="Videre!" id="estimateSettingsSubmit" type="submit" class="btn btn-primary" />
        </form>
    </div>
</div>
@endsection
