@extends($layout)

@section('content')

    <x-global::pageheader :icon="'fa fa-table'">
        <h1>Import To-Dos</h1>
    </x-global::pageheader>
    @if(isset($tpl))
        <link rel="stylesheet" href="{{$tpl->get('importStyling')}}"/>
        <script type="module" src="{{$tpl->get('importScript')}}"></script>

        <div class="estimateimport-content maincontent">
            {{$tpl->displayNotification()}}
            <div class="maincontentinner">

                <ol class="steps">
                    <li class="step is-complete" data-step="1">
                        Upload file
                    </li>
                    <li class="step is-complete" data-step="2">
                        Map fields
                    </li>
                    <li class="step is-active" data-step="3">
                        Review
                    </li>
                </ol>
                <form enctype="multipart/form-data" action="" method="post">

                        <?php
                        if (isset($tpl)) {
                            $mappings = $tpl->get('mappings') ?? [];
                            $supportedFields = $tpl->get('supportedFields') ?? [];
                            if (!empty($validationWarnings)) {
                                echo "<h2>Warnings:</h2>";
                                foreach ($validationWarnings as $validationWarning) {
                                    echo "<p>" . $validationWarning . "</p>";
                                }
                            }
                            if (!empty($validationErrors)) {
                                echo "</br></br>";
                                echo "<h2>Errors:</h2>";
                                foreach ($validationErrors as $errorGroupName => $errorGroup) {
                                    switch ($errorGroupName) {
                                        case "Milestone":
                                            echo "<div class='validation-error-headline'><h3>" . $errorGroupName . " (" . count($errorGroup) . " errors)</h3><span data-subject='" . $errorGroupName . "' class='btn btn-primary validation-fix-button'>Add milestones</span></div>";
                                            break;
                                        default:
                                            echo "<div class='validation-error-headline'><h3>" . $errorGroupName . " (" . count($errorGroup) . " errors)</h3></div>";
                                            break;
                                    }

                                    foreach ($errorGroup as $errorSubject => $errorSpec) {
                                        echo "<p>" . $errorSpec . " <b>" . $errorSubject . "</b></p>";

                                    }
                                }
                            }

                            echo "</br></br>";

                            $dataToValidate = $tpl->get('dataToValidate') ?? [];
                            foreach ($dataToValidate as $count => $dataArray) {
                                echo "<div class='ticket-header'><label for='field-" . $count . "'>Ticket " . ($count + 1) . "</label><input id='field-" . $count . "' type='checkbox' checked name='dataImportConfirmation[]' value='" . $count . "' /></div>";
                                foreach ($dataArray as $field => $datumToValidate) {
                                    $field = str_replace(' ', '_', $field);
                                    echo "<div class='ticket-content'><span>" . $supportedFields[$mappings[$field]]["name"] . "</span> <i class='fa-solid fa-arrow-right'></i> <span>" . $datumToValidate . "</span></div>";

                                }
                                echo "<br />";
                            }
                        }
                        ?>

                    <div class="form-group">
                        <label for="dataValidated"><input id="dataValidated" type="checkbox" name="dataValidated"/> {{--TODO: Implement proper translations--}}
                            <span>I am sure that the data is mapped correcly and i want to import it into Leantime.</span></label>
                    </div>
                    <br/>

                    <a href="/EstimateImport/importMapping" class="btn btn-inverse">Tilbage</a>
                    <input type="submit" value="Videre!" id="validateMappingSubmit" disabled type="submit"
                           class="btn btn-primary"/>
                </form>

            </div>
        </div>
    @endif
@endsection
