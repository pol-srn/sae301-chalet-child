/**
 * Page Commande (IT-20) : le jour choisi affiche ses créneaux, les autres sont masqués.
 * Un créneau coché sur un autre jour est décoché pour éviter de commander sur un jour non affiché.
 */
( function () {
	const bloc = document.querySelector( '.creneau' );
	if ( ! bloc ) {
		return;
	}

	bloc.addEventListener( 'change', function ( evenement ) {
		if ( 'chalet_jour' !== evenement.target.name ) {
			return;
		}
		bloc.querySelectorAll( '.creneau__heures' ).forEach( function ( groupe ) {
			const visible = groupe.dataset.jour === evenement.target.value;
			groupe.hidden = ! visible;
			if ( ! visible ) {
				groupe.querySelectorAll( 'input:checked' ).forEach( ( champ ) => ( champ.checked = false ) );
			}
		} );
	} );
}() );
