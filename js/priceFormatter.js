function normalizePriceValue(value) {
  if (typeof value !== "string") {
    return "";
  }

  var normalized = value.trim().replace(/[\s\u00A0\u2007\u202F]+/g, "");

  if (!normalized || /[^0-9.,]/.test(normalized)) {
    return "";
  }

  var lastComma = normalized.lastIndexOf(",");
  var lastDot = normalized.lastIndexOf(".");
  var decimalIndex = Math.max(lastComma, lastDot);
  var integerPart = normalized;
  var fractionPart = "";

  if (decimalIndex !== -1) {
    integerPart = normalized.slice(0, decimalIndex);
    fractionPart = normalized.slice(decimalIndex + 1);
  }

  integerPart = integerPart.replace(/[.,]/g, "");
  fractionPart = fractionPart.replace(/[.,]/g, "");

  if (!integerPart) {
    integerPart = "0";
  }

  if (!/^\d+$/.test(integerPart) || (fractionPart && !/^\d+$/.test(fractionPart))) {
    return "";
  }

  return fractionPart ? integerPart + "." + fractionPart : integerPart;
}

function formatPriceForDisplay(value) {
  var normalized = normalizePriceValue(value);

  if (!normalized) {
    return value;
  }

  var parts = normalized.split(".");
  var integerNumber = Number(parts[0]);

  if (!Number.isFinite(integerNumber)) {
    return value;
  }

  var minimumFractionDigits = 0;
  var maximumFractionDigits = 2;

  if (parts.length > 1) {
    minimumFractionDigits = Math.min(parts[1].length, 2);
  }

  return new Intl.NumberFormat("cs-CZ", {
    minimumFractionDigits: minimumFractionDigits,
    maximumFractionDigits: maximumFractionDigits,
  }).format(Number(normalized));
}

function bindPriceFormatter(input) {
  if (!input) {
    return;
  }

  input.addEventListener("blur", function () {
    var formatted = formatPriceForDisplay(input.value);

    if (formatted !== "") {
      input.value = formatted;
    }
  });

  input.addEventListener("focus", function () {
    var normalized = normalizePriceValue(input.value);

    if (normalized !== "") {
      input.value = normalized.replace(".", ",");
    }
  });

  var form = input.form;

  if (!form || form.dataset.priceFormatterBound === "true") {
    return;
  }

  form.dataset.priceFormatterBound = "true";
  form.addEventListener("submit", function () {
    var priceInputs = form.querySelectorAll("[data-price-input]");

    priceInputs.forEach(function (field) {
      var normalized = normalizePriceValue(field.value);

      if (normalized !== "") {
        field.value = normalized;
      }
    });
  });
}

document.addEventListener("DOMContentLoaded", function () {
  var priceInputs = document.querySelectorAll("[data-price-input]");

  priceInputs.forEach(function (input) {
    bindPriceFormatter(input);

    if (input.value) {
      input.value = formatPriceForDisplay(input.value);
    }
  });
});
