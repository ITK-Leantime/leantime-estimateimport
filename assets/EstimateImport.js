window.addEventListener("load", (event) => {
  const dataValidated = document.getElementById('dataValidated');
  const validateMappingSubmit = document.getElementById('validateMappingSubmit');
  const mapperSelect = document.getElementsByClassName('mapper-select');

  dataValidated?.addEventListener('change', () => {
      validateMappingSubmit.disabled = !dataValidated.checked;
  });


  // Loop through all elements with the class 'mapper-select'
  for (let i = 0; i < mapperSelect.length; i++) {
      mapperSelect[i].addEventListener('change', function() {
          // Get the parent element

          // Get the next sibling that is a <span> element
          var nextSpan = this.nextElementSibling;
          while (nextSpan && nextSpan.tagName !== 'SPAN') {
              nextSpan = nextSpan.nextElementSibling;
          }

          // Set the text content of the next <span> element to the value of the data attribute 'data-help' of the selected option
          if (nextSpan) {
              var selectedIndex = this.selectedIndex;
              if (selectedIndex !== -1) {
                  var selectedOption = this.options[selectedIndex];
                  nextSpan.textContent = selectedOption.getAttribute('data-help');
              }
          }
      });
  }
});
