var form1 = document.getElementById("form1");
var form2 = document.getElementById("form2");
var form3 = document.getElementById("form3");

var next1 = document.getElementById("next1");
var next2 = document.getElementById("next2");

var back1 = document.getElementById("back1");
var back2 = document.getElementById("back2");

var progress = document.getElementById("progress");

next1.onclick = function () {
  form1.style.left = "-700px";
  form2.style.left = "100px";
  progress.style.width = "340px";
};

back1.onclick = function () {
  form1.style.left = "100px";
  form2.style.left = "700px";
  progress.style.width = "120px";
};

next2.onclick = function () {
  form2.style.left = "-700px";
  form3.style.left = "100px";
  progress.style.width = "500px";
};

back2.onclick = function () {
  form2.style.left = "100px";
  form3.style.left = "700px";
  progress.style.width = "340px";
};

// document.addEventListener("DOMContentLoaded", () => {
//   const firstSelect = document.getElementById("selecter");
//   const selectContainer = document.getElementById("selectid");

//   // Contenu des options pour le deuxième select selon la sélection du premier
//   const optionsMapping = {
//     "1": [
//       { value: "94", text: "Création d'une affiche" },
//     ],
//     "2": [
//       { value: "9", text: "Accès Informatique" },
//       { value: "7", text: "Applications métier" },
//       { value: "87", text: "GESSI" },
//       { value: "8", text: "Matériel Informatique" },
//     ],
//     "3": [
//       { value: "19", text: "Téléphone Fixe" },
//       { value: "18", text: "Smartphone et GSM" },
//     ],
//   };

//   firstSelect.addEventListener("change", (event) => {
//     const selectedValue = event.target.value;

//     // Supprimer l'ancien select (s'il existe déjà)
//     const existingSecondSelect = document.getElementById("secondSelect");
//     if (existingSecondSelect) {
//       existingSecondSelect.remove();
//     }

//     // Ajouter un nouveau select uniquement si une option valide est choisie
//     if (optionsMapping[selectedValue]) {
//       const secondSelect = document.createElement("select");
//       secondSelect.id = "secondSelect";
//       secondSelect.name = "subcategory"; // Ajout du paramètre name
//       secondSelect.className = "form1-select";

//       // Ajouter une option par défaut
//       const defaultOption = document.createElement("option");
//       defaultOption.textContent = "Choisissez une option";
//       defaultOption.value = "";
//       secondSelect.appendChild(defaultOption);

//       // Ajouter les options au deuxième select
//       optionsMapping[selectedValue].forEach((optionData) => {
//         const option = document.createElement("option");
//         option.value = optionData.value; // Valeur de l'option
//         option.textContent = optionData.text; // Texte de l'option
//         secondSelect.appendChild(option);
//       });

//       // Ajouter le nouveau select au conteneur
//       selectContainer.appendChild(secondSelect);
//     }
//   });
// });
