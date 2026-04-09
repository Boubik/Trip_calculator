document.addEventListener("DOMContentLoaded", function () {
  var navbarToggle = document.getElementById("navbar-toggle");
  var navList = document.querySelector("nav ul");

  if (navbarToggle && navList) {
    navbarToggle.addEventListener("click", function (event) {
      event.preventDefault();

      if (navList.style.display === "block") {
        navList.style.display = "";
      } else {
        navList.style.display = "block";
      }

      navbarToggle.classList.toggle("active");
    });
  }

  document
    .querySelectorAll("nav ul li a:not(:only-child)")
    .forEach(function (link) {
      link.addEventListener("click", function (event) {
        var dropdown = link.nextElementSibling;

        if (!dropdown || !dropdown.classList.contains("navbar-dropdown")) {
          return;
        }

        event.preventDefault();

        document.querySelectorAll(".navbar-dropdown").forEach(function (item) {
          if (item !== dropdown) {
            item.style.display = "none";
          }
        });

        dropdown.style.display =
          dropdown.style.display === "block" ? "none" : "block";
      });
    });

  document.addEventListener("click", function (event) {
    if (!event.target.closest("nav")) {
      document.querySelectorAll(".navbar-dropdown").forEach(function (item) {
        item.style.display = "none";
      });
    }
  });
});
