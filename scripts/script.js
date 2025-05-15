$(document).ready(function () {
  $("#login").click(function () {
    window.location.href = "login/login.php";
  });

  $("#register").click(function () {
    window.location.href = "login/register.php";
  });

  $("#logout").click(function () {
    window.location.href = "login/logout.php";
  });

  $("#verslag-toevoegen").click(function () {
    window.location.href = "php/verslag_toevoegen.php";
  });

  $(".lees-meer").on("click", function () {
    var moreText = $(this).parent().find("#more");
    moreText.slideToggle();

    if ($(this).text() === "Lees meer...") {
      $(this).text("Lees minder...");
    } else {
      $(this).text("Lees meer...");
    }
  });

  $("#info-btn").click(function () {
    // Toggle zichtbaarheid van de informatie
    $("#wedstrijd-info").slideToggle();

    // Controleer of de wedstrijdinformatie zichtbaar is
    if ($(this).text() === "Verberg") {
      $(this).text("Toon");
    } else {
      $(this).text("Verberg");
    }
  });
});
