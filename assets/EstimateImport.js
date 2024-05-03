window.addEventListener("load", (event) => {
  const dataValidated = document.getElementById("dataValidated");
  const validateMappingSubmit = document.getElementById(
    "validateMappingSubmit",
  );
  const mapperSelect = document.getElementsByClassName("mapper-select");

  const validationFixButton = document.getElementsByClassName(
    "validation-fix-button",
  );
  dataValidated?.addEventListener("change", () => {
    validateMappingSubmit.disabled = !dataValidated.checked;
  });

  for (let i = 0; i < validationFixButton.length; i++) {
    validationFixButton[i].addEventListener("click", function (e) {
      const subject = e.target.getAttribute("data-subject");

      switch (subject) {
        case "Milestone":
          e.target.innerHTML =
            "Adding milestones <i class='fa fa-fw fa-spinner fa-spin'></i>";
          async function fetchData() {
            let response = await fetch(
              "/EstimateImport/importValidation?fixErrors=" + subject,
            );
            let data = await response.json();
            if (data) {
              window.location.reload();
            }
          }
          fetchData();
          break;
        default:
          console.log("not yet implemented");
          break;
      }
    });
  }
  // Loop through all elements with the class 'mapper-select'
  for (let i = 0; i < mapperSelect.length; i++) {
    mapperSelect[i].addEventListener("change", function () {
      // Get the parent element

      // Get the next sibling that is a <span> element
      var nextSpan = this.nextElementSibling;
      while (nextSpan && nextSpan.tagName !== "SPAN") {
        nextSpan = nextSpan.nextElementSibling;
      }

      // Set the text content of the next <span> element to the value of the data attribute 'data-help' of the selected option
      if (nextSpan) {
        var selectedIndex = this.selectedIndex;
        if (selectedIndex !== -1) {
          var selectedOption = this.options[selectedIndex];
          nextSpan.textContent = selectedOption.getAttribute("data-help");
        }
      }
    });
  }
});
