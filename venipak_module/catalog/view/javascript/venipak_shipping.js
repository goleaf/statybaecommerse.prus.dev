document.addEventListener("DOMContentLoaded", function () {
  const targetNode = document.body;
  if (!targetNode) {
    console.error("Target node not found.");
    return;
  }

  const config = {
    childList: true,
    subtree: true,
  };

  let venipakShippingOptions = [];
  let lastLabel = null;
  let selectedRadioValue = null;

  const findVenipakShippingInputs = () => {
    selectedRadioValue = null;
    const venipakInputs = document.querySelectorAll(
      'input[type="radio"][name="shipping_method"][value*="venipak_shipping.venipak_shipping_pickup_"]'
    );

    if (venipakInputs.length > 0) {
      venipakShippingOptions = [];
    } else {
      return;
    }
    venipakInputs.forEach((input, index) => {
      if (input.checked) {
        selectedRadioValue = input.value;
      }
      const label = input.closest("div.form-check");

      if (label) {
        const inputValue = input.value;
        const labelText = label.textContent.trim();

        if (
          !venipakShippingOptions.some((option) => option.key === inputValue)
        ) {
          venipakShippingOptions.push({ key: inputValue, label: labelText });
        }

        lastLabel = label;

        if (index !== venipakInputs.length - 1) {
          label.remove();
        }
      }
    });

    if (
      venipakShippingOptions.length > 0 &&
      lastLabel &&
      !lastLabel.querySelector(".venipak-pickup-dropdown")
    ) {
      addSelectDropdownWithRadio(lastLabel, selectedRadioValue);
    }
  };
  function addSelectDropdownWithRadio(replaceLabel, preselectedValue) {
    replaceLabel.innerHTML = "";

    const radioDiv = document.createElement("div");
    radioDiv.classList.add("form-check");

    const radioInput = document.createElement("input");
    radioInput.type = "radio";
    radioInput.name = "shipping_method";
    radioInput.value = ""; // Initially empty, will be set based on select
    radioInput.classList.add("venipak-radio");
    radioInput.id =
      "input-shipping-method-venipak_shipping-venipak-shipping-pickup";

    const label = document.createElement("label");
    label.htmlFor = radioInput.id; // Link the label to the radio input using `htmlFor`

    // Create the span for the label (title)
    const selectLabelElement = document.createElement("span");
    selectLabelElement.style.verticalAlign = "middle"; // Align label vertically
    selectLabelElement.style.display = "inline-block";

    let firstPart = "";
    let lastPart = "";

    venipakShippingOptions.forEach((option) => {
      const labelText = option.label.trim();

      // Find the first hyphen and split before it
      const firstHyphenIndex = labelText.indexOf("-");
      if (firstHyphenIndex !== -1) {
        firstPart = labelText.slice(0, firstHyphenIndex).trim(); // Get the part before the first hyphen
      }

      const lastParenthesisIndex = labelText.lastIndexOf(")");
      if (
        lastParenthesisIndex !== -1 &&
        lastParenthesisIndex < labelText.length - 1
      ) {
        lastPart = labelText.slice(lastParenthesisIndex + 1).trim();
        if (lastPart.startsWith("-")) {
          lastPart = lastPart.slice(1).trim();
        }
      }

      if (firstPart && lastPart) {
        const selectLabel = `${firstPart} - ${lastPart}`;
        selectLabelElement.textContent = selectLabel;
      }
    });

    const selectDropdown = document.createElement("select");
    selectDropdown.name = "venipak-select";
    selectDropdown.classList.add("venipak-pickup-dropdown", "form-control");
    selectDropdown.style.marginTop = "8px";
    selectDropdown.style.marginLeft = "8px";
    selectDropdown.style.width = "50%";

    const defaultOption = document.createElement("option");
    defaultOption.textContent = "-";
    defaultOption.value = "";
    selectDropdown.appendChild(defaultOption);

    venipakShippingOptions.forEach((option) => {
      const dropdownOption = document.createElement("option");

      const labelText = option.label.trim();
      const firstHyphenIndex = labelText.indexOf("-");
      let middlePart = "";

      if (firstHyphenIndex !== -1) {
        middlePart = labelText.slice(firstHyphenIndex + 1).trim();
      }
      const lastParenthesisIndex = middlePart.lastIndexOf(")");
      if (lastParenthesisIndex !== -1) {
        middlePart = middlePart.slice(0, lastParenthesisIndex + 1).trim();
      }

      dropdownOption.value = option.key;
      dropdownOption.textContent = middlePart;

      if (middlePart) {
        selectDropdown.appendChild(dropdownOption);
      }
    });

    if (preselectedValue) {
      selectDropdown.value = preselectedValue;
      radioInput.value = preselectedValue;
      radioInput.checked = true;
    }

    $(selectDropdown).on("change", function () {
      radioInput.value = selectDropdown.value;
      radioInput.checked = true;
      if (typeof MPSHIPPINGMETHODS !== "undefined") {
        MPSHIPPINGMETHODS.save(true);
      }
    });

    // Append the elements in the correct order
    radioDiv.appendChild(radioInput);
    label.appendChild(selectLabelElement);
    radioDiv.appendChild(label);
    radioDiv.appendChild(selectDropdown);

    replaceLabel.replaceWith(radioDiv);
    const language = async () => {
      const response = await fetch('index.php?route=extension/venipak_shipping/shipping/venipak_shipping.pickupSelectBoxLanguageTranslation');
      const data = await response.json();  
      return data.text_pickup_select_option; 
  }  
  language().then((text) => {  
    $(selectDropdown).select2({
      placeholder: text,
      allowClear: true,
      width: "100%",
      selectOnClose: true,
      dropdownParent: $("#modal-shipping"),
    });
  });
  }

  const callback = (mutationsList, observer) => {
    for (const mutation of mutationsList) {
      if (mutation.type === "childList") {
        observer.disconnect();
        findVenipakShippingInputs();
        observer.observe(targetNode, config);
      }
    }
  };

  const observer = new MutationObserver(callback);
  observer.observe(targetNode, config);
});
