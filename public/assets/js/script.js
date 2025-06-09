/**
 * JS minimal pour d'éventuelles interactions futures.
 * Pour le moment, on peut le garder vide ou y ajouter
 * de petites améliorations plus tard (ex. validation client).
 */

document.addEventListener('DOMContentLoaded', function() {
  // Exemple : Ajouter un petit effet sur les boutons si besoin
  const buttons = document.querySelectorAll('button, .button');
  buttons.forEach(btn => {
    btn.addEventListener('mouseover', () => {
      btn.style.opacity = '0.9';
    });
    btn.addEventListener('mouseout', () => {
      btn.style.opacity = '1';
    });
  });
});
