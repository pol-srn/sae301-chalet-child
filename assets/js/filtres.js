/**
 * Panneau « Filtrer et trier » (IT-18) : le bouton en haut de la carte amène la carte en haut de l'écran
 * puis ouvre le panneau ; la croix, le voile ou Échap le ferment. Pendant l'ouverture, la page ne défile plus.
 * À chaque choix, les nombres de plats sont recalculés (même logique que WooCommerce : OU dans un groupe,
 * ET entre les groupes, fourchette de prix) ; les choix sans résultat sont grisés.
 */
( function () {
	const bouton = document.querySelector( '.carte-filtres__bouton' );
	const panneau = document.getElementById( 'filtres-carte' );
	const voile = document.querySelector( '.filtres-voile' );
	const zone = document.querySelector( '.carte-zone' );

	if ( ! bouton || ! panneau || ! voile || ! zone ) {
		return;
	}

	const fermer = panneau.querySelector( '.filtres__fermer' );
	const formulaire = panneau.querySelector( '.filtres__formulaire' );
	const plats = JSON.parse( formulaire.dataset.plats || '[]' );
	const cases = Array.from( formulaire.querySelectorAll( 'input[type="checkbox"]' ) );
	const prixMin = formulaire.querySelector( '.filtre__plage--min' );
	const prixMax = formulaire.querySelector( '.filtre__plage--max' );
	const voir = formulaire.querySelector( '.filtres__voir' );
	const resume = formulaire.querySelector( '.filtres__nombre' );

	/* ---------- Ouverture / fermeture ---------- */

	function ouvrir() {
		// La carte remonte en haut de l'écran (sous la barre d'admin si elle est affichée), le panneau part de là.
		const barre = document.getElementById( 'wpadminbar' );
		const haut = barre ? barre.offsetHeight : 0;
		window.scrollTo( { top: window.scrollY + zone.getBoundingClientRect().top - haut, behavior: 'instant' } );
		document.documentElement.style.setProperty( '--filtres-haut', haut + 'px' );
		document.documentElement.classList.add( 'filtres-ouverts' );
		panneau.hidden = false;
		voile.hidden = false;
		bouton.setAttribute( 'aria-expanded', 'true' );
		fermer.focus();
	}

	function refermer() {
		document.documentElement.classList.remove( 'filtres-ouverts' );
		panneau.hidden = true;
		voile.hidden = true;
		bouton.setAttribute( 'aria-expanded', 'false' );
		bouton.focus();
	}

	bouton.addEventListener( 'click', ouvrir );
	fermer.addEventListener( 'click', refermer );
	voile.addEventListener( 'click', refermer );

	document.addEventListener( 'keydown', function ( evenement ) {
		if ( 'Escape' === evenement.key && ! panneau.hidden ) {
			refermer();
		}
	} );

	/* ---------- Calcul en direct ---------- */

	const pluriel = ( n, mot ) => n + ' ' + mot + ( n > 1 ? 's' : '' );

	// Choix cochés par groupe : { filter_taille: ['bonne-faim', …], … }.
	function choix() {
		const groupes = {};
		cases.forEach( function ( c ) {
			const groupe = c.name.replace( '[]', '' );
			groupes[ groupe ] = groupes[ groupe ] || [];
			if ( c.checked ) {
				groupes[ groupe ].push( c.value );
			}
		} );
		return groupes;
	}

	function fourchette() {
		if ( ! prixMin || ! prixMax ) {
			return [ -Infinity, Infinity ];
		}
		const a = Number( prixMin.value );
		const b = Number( prixMax.value );
		return [ Math.min( a, b ), Math.max( a, b ) ];
	}

	// Un plat correspond-il aux choix, en ignorant éventuellement un groupe ?
	function correspond( plat, groupes, prix, sauf ) {
		if ( plat.max < prix[ 0 ] || plat.min > prix[ 1 ] ) {
			return false;
		}
		return Object.keys( groupes ).every( function ( groupe ) {
			const valeurs = groupes[ groupe ];
			return groupe === sauf || ! valeurs.length || valeurs.some( ( v ) => ( plat.attrs[ groupe ] || [] ).includes( v ) );
		} );
	}

	function curseur() {
		if ( ! prixMin || ! prixMax ) {
			return;
		}
		const prix = fourchette();
		const etendue = Number( prixMin.max ) - Number( prixMin.min ) || 1;
		const gauche = ( ( prix[ 0 ] - prixMin.min ) / etendue ) * 100;
		const droite = ( ( prix[ 1 ] - prixMin.min ) / etendue ) * 100;
		const piste = formulaire.querySelector( '.filtre__curseur' );
		piste.style.setProperty( '--gauche', gauche + '%' );
		piste.style.setProperty( '--droite', droite + '%' );
		formulaire.querySelector( '.filtre__montant-min' ).textContent = prix[ 0 ] + ' €';
		formulaire.querySelector( '.filtre__montant-max' ).textContent = prix[ 1 ] + ' €';
	}

	function recalculer() {
		const groupes = choix();
		const prix = fourchette();

		cases.forEach( function ( c ) {
			const groupe = c.name.replace( '[]', '' );
			const nombre = plats.filter( ( p ) => correspond( p, groupes, prix, groupe ) && ( p.attrs[ groupe ] || [] ).includes( c.value ) ).length;
			const ligne = c.closest( '.filtre__ligne' );
			ligne.querySelector( '.filtre__nombre' ).textContent = pluriel( nombre, 'plat' );
			c.disabled = 0 === nombre && ! c.checked;
			ligne.classList.toggle( 'filtre__ligne--vide', c.disabled );
		} );

		const total = plats.filter( ( p ) => correspond( p, groupes, prix, null ) ).length;
		voir.textContent = total ? 'Voir ' + ( total > 1 ? 'les ' + total + ' plats' : 'le plat' ) : 'Aucun plat';
		voir.disabled = 0 === total;

		const prixActif = prixMin && ( prix[ 0 ] > Number( prixMin.min ) || prix[ 1 ] < Number( prixMin.max ) );
		const nombreChoix = cases.filter( ( c ) => c.checked ).length + ( prixActif ? 1 : 0 );
		resume.textContent = nombreChoix ? pluriel( nombreChoix, 'filtre' ) + ' sélectionné' + ( nombreChoix > 1 ? 's' : '' ) : '';

		curseur();
	}

	formulaire.addEventListener( 'input', recalculer );
	formulaire.addEventListener( 'change', recalculer );
	recalculer();
}() );
