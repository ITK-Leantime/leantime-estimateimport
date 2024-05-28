@extends($layout)

@section('content')

<x-global::pageheader :icon="'fa fa-table'">
    <h1>Import To-Dos</h1>
</x-global::pageheader>
<link rel="stylesheet"  href="{{ $tpl->get('importStyling') }}" />
<script type="module" src="{{ $tpl->get('importScript' )}}"></script>

<div class="estimateimport-content maincontent">
    {{ $tpl->displayNotification() }}
    <div class="maincontentinner">
    <ol class="steps">
  <li class="step is-complete" data-step="1">
    Upload file
  </li>
  <li class="step is-active" data-step="2">
    Map fields
  </li>
  <li class="step" data-step="3">
    Review
  </li>
</ol>
        <form enctype="multipart/form-data" action="" method="post">
            <div class="mapper-container">
                <?php

                if (isset($tpl)) {
                    $estimateFileHeaders = $tpl->get('estimateFileHeaders') ?? [];
                    $supportedFields = $tpl->get('supportedFields') ?? [];

                    foreach ($estimateFileHeaders as $header) {
                        if (empty($header)) {
                            continue;
                        }
                        echo "<div class='mapper-div'>";
                        echo "<div class='mapper-header'><span>{$header}</span></div>";
                        echo "<div class='mapper-pointer'><span>→</span></div>";
                        echo "<div class='mapper-value'><select class='mapper-select' name='{$header}'>";
                        echo "<option value='-1'>Don't map this field</option>";
                        foreach ($supportedFields as $key => $value) {
                            echo "<option data-help='{$value['help']}' value='{$key}'>{$value['name']}</option>";
                        }
                        echo "</select><span class='text-warning'></span></div>";
                        echo "</div>";
                    }
                }
                ?>
            </div>
            <div class="d-block">
        <input type="submit" value="Videre!" id="estimateMappingSubmit" type="submit" class="btn btn-primary" />
            </div>
        </form>

    </div>
</div>
@endsection
