@extends($layout)

@section('content')

<x-global::pageheader :icon="'fa fa-table'">
    <h1>Import To-Dos</h1>
</x-global::pageheader>
<link rel="stylesheet"  href="<?php echo $tpl->get('importStyling') ?>" />
<script type="module" src="<?php echo $tpl->get('importScript') ?>"></script>

<div class="estimateimport-content maincontent">
<?php echo $tpl->displayNotification(); ?>
    <div class="maincontentinner">

    <ol class="steps">
  <li class="step is-active" data-step="1">
    Upload & Settings
  </li>
  <li class="step" data-step="2">
    Map fields
  </li>
  <li class="step" data-step="3">
    Review
  </li>
</ol>
        <form enctype="multipart/form-data" action="" method="post">
        <div class="form-group">
                <label for="estimateFile">Estimate file</label>
                <input required class="fileupload" name="estimateFile" id="estimateUpload" accept=".csv" type="file" />
            </div>
        <div class="form-group">
                <label for="fileEncoding">File encoding</label>
                <select required name="fileEncoding"><option>UTF-8</option></select>
            </div>

            <div class="form-group">
                <label for="delimiter">Delimiter</label>
                <input required name="delimiter" type="text" required placeholder="Delimiter" value=";" />
            </div>

            <div class="form-group">
                <label for="projectId">Project</label>
                <select name="projectId">
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
        <input type="submit" value="Videre!" id="estimateUploadSubmit" type="submit" class="btn btn-primary" />
        </form>

    </div>
</div>
@endsection
