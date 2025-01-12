function changePasswords() {
    if (document.getElementById("newPassword").value.length >= 6 && document.getElementById("newPassword").value == document.getElementById("newPassword2").value) {
        var hash1 = CryptoJS.SHA3(document.getElementById("oldPassword").value, {
            outputLength: 512
        });
        var hash2 = CryptoJS.SHA3(document.getElementById("newPassword").value, {
            outputLength: 512
        });
        var hash3 = CryptoJS.SHA3(document.getElementById("newPassword2").value, {
            outputLength: 512
        });
        document.getElementById("oldPassword").value = hash1;
        document.getElementById("newPassword").value = hash2;
        document.getElementById("newPassword2").value = hash3;
    }
}
