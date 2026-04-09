function fillLegacyHash(passwordFieldId, hiddenFieldId) {
    if (typeof CryptoJS === "undefined") {
        return true;
    }

    var passwordField = document.getElementById(passwordFieldId);
    var hiddenField = document.getElementById(hiddenFieldId);

    if (!passwordField || !hiddenField) {
        return true;
    }

    hiddenField.value = '';

    if (passwordField.value.length === 0) {
        return true;
    }

    hiddenField.value = CryptoJS.SHA3(passwordField.value, {
        outputLength: 512
    }).toString();

    return true;
}

function changePasswords() {
    return fillLegacyHash('oldPassword', 'oldPasswordLegacy');
}

document.addEventListener('DOMContentLoaded', function () {
    var form = document.querySelector('form');

    if (!form) {
        return;
    }

    form.addEventListener('submit', function () {
        changePasswords();
    });
});
