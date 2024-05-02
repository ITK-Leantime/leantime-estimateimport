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
  <li class="step is-active" data-step="1">
    Upload file
  </li>
  <li class="step" data-step="2">
    Settings
  </li>
  <li class="step" data-step="3">
    Map fields
  </li>
  <li class="step" data-step="4">
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

        <br />
        <input type="submit" value="Videre!" id="estimateUploadSubmit" type="submit" class="btn btn-primary" />
        </form>

    </div>
</div>
@endsection
