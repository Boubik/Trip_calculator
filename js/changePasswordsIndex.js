function changePasswords() {
  if (document.getElementById("password").value.length != 0) {
    var hash = CryptoJS.SHA3(document.getElementById("password").value, {
      outputLength: 512,
    });
    document.getElementById("password").value = hash;
  }
}
