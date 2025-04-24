// global TrelloPowerUp
document.addEventListener("DOMContentLoaded", function () {
  var GREY_ROCKET_ICON = "./assets/iconBN.png";

  TrelloPowerUp.initialize({
    "card-buttons": function (t, options) {
      return t.card("name", "desc").then(function (card) {
        let id = options.context.card;
        let titulo = card.name;
        let descripcion = card.desc;
        let idioma = navigator.language || navigator.userLanguage; // Obtén el idioma del navegador

        let cardData = {
          id: id,
          titulo: titulo,
          descripcion: descripcion,
          idioma: idioma, // Agrega el idioma a los datos de la tarjeta
        };

        localStorage.setItem("cardData", JSON.stringify(cardData));

        return [
          {
            icon: GREY_ROCKET_ICON,
            text: "GPT-4 Autocomplete",
            callback: function (t) {
              return t.popup({
                title: "GPT-4 For Trello",
                url: "estimate.html",
                height: 450,
              });
            },
          },
        ];
      });
    },
    "on-enable": function (t, options) {
      return Promise.resolve();
    },
    "board-buttons": function (t, options) {
      return Promise.resolve();
    },
    "card-badges": function (t, options) {
      return Promise.resolve();
    },
    "show-authorization": function (t, options) {
      return Promise.resolve();
    },
    "authorization-status": function (t, options) {
      return Promise.resolve();
    },
    "show-settings": function (t, options) {
      return Promise.resolve();
    },
    "format-url": function (t, options) {
      return Promise.resolve();
    },
    "remove-data": function (t, options) {
      return Promise.resolve();
    },
    "on-disable": function (t, options) {
      return Promise.resolve();
    },
    "list-sorters": function (t, options) {
      return Promise.resolve();
    },
    "list-actions": function (t, options) {
      return Promise.resolve();
    },
    "card-from-url": function (t, options) {
      return Promise.resolve();
    },
    "card-detail-badges": function (t, options) {
      return Promise.resolve();
    },
    "card-back-section": function (t, options) {
      return Promise.resolve();
    },
    "attachment-thumbnail": function (t, options) {
      return Promise.resolve();
    },
    "attachment-sections": function (t, options) {
      return Promise.resolve();
    },
    "save-attachment": function (t, options) {
      return Promise.resolve();
    },
  });
});
