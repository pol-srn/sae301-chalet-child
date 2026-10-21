/**
 * Menu plein écran du Chalet : le bouton burger ouvre le <dialog>, la croix le ferme.
 * Échap, focus et fond inerte : gérés par showModal() du navigateur.
 */
( function () {
	const bouton = document.querySelector( '.entete__lien--menu' );
	const menu = document.getElementById( 'menu-principal' );

	if ( ! bouton || ! menu || typeof menu.showModal !== 'function' ) {
		return;
	}

	bouton.addEventListener( 'click', function () {
		menu.showModal();
		bouton.setAttribute( 'aria-expanded', 'true' );
	} );

	// Fermeture (croix ou Échap) : on rend le focus au bouton burger.
	menu.addEventListener( 'close', function () {
		bouton.setAttribute( 'aria-expanded', 'false' );
		bouton.focus();
	} );
}() );
