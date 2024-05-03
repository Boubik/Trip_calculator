$(".form-signin").submit(function () {
  if ($("#password").val().length !== 0) {
    var hash = CryptoJS.SHA3($("#password").val(), {
      outputLength: 512,
    });
    $("#password").val(hash);
  }
});
