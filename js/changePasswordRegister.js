function changePasswords() {
    if (document.getElementById("password1").value.length >= 6 && document.getElementById("password1").value == document.getElementById("password2").value) {
        var hash1 = CryptoJS.SHA3(document.getElementById("password1").value, {
            outputLength: 512
        });
        var hash2 = CryptoJS.SHA3(document.getElementById("password2").value, {
            outputLength: 512
        });
        document.getElementById("password1").value = hash1;
        document.getElementById("password2").value = hash2;
    }
}
