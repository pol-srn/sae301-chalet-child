<?php
/**
 * Mon compte du Chalet : connexion et création de compte (visiteur non connecté).
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package chalet-child
 * @version 9.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$inscription = 'yes' === get_option( 'woocommerce_enable_myaccount_registration' );

do_action( 'woocommerce_before_customer_login_form' );
?>
<div class="compte-acces" id="customer_login">
	<p class="compte-acces__intro">Retrouvez vos commandes et commandez plus vite.</p>

	<div class="compte-acces__cartes">
		<section class="bon compte-acces__carte" aria-labelledby="titre-connexion">
			<h2 class="bon__titre" id="titre-connexion">Se connecter</h2>
			<p class="bon__sous-titre">Heureux de vous revoir au Chalet.</p>

			<form class="woocommerce-form woocommerce-form-login login compte-acces__formulaire" method="post" novalidate>
				<?php do_action( 'woocommerce_login_form_start' ); ?>

				<p class="bon__champ">
					<label class="bon__libelle" for="username">E-mail ou identifiant</label>
					<input type="text" class="bon__saisie woocommerce-Input input-text" name="username" id="username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) && is_string( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" required aria-required="true"><?php // phpcs:ignore WordPress.Security.NonceVerification ?>
				</p>
				<p class="bon__champ">
					<label class="bon__libelle" for="password">Mot de passe</label>
					<input type="password" class="bon__saisie woocommerce-Input input-text" name="password" id="password" autocomplete="current-password" required aria-required="true">
				</p>

				<?php do_action( 'woocommerce_login_form' ); ?>

				<p class="compte-acces__case">
					<input class="woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever">
					<label for="rememberme">Se souvenir de moi</label>
				</p>
				<p class="compte-acces__lien">
					<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>">Mot de passe perdu ?</a>
				</p>
				<p class="compte-acces__action">
					<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
					<button type="submit" class="button compte__bouton woocommerce-form-login__submit" name="login" value="Se connecter">Se connecter</button>
				</p>

				<?php do_action( 'woocommerce_login_form_end' ); ?>
			</form>
		</section>

		<?php if ( $inscription ) : ?>
			<section class="bon compte-acces__carte" aria-labelledby="titre-inscription">
				<h2 class="bon__titre" id="titre-inscription">Créer un compte</h2>
				<p class="bon__sous-titre">Suivez vos commandes et gagnez du temps.</p>

				<form method="post" class="woocommerce-form woocommerce-form-register register compte-acces__formulaire" <?php do_action( 'woocommerce_register_form_tag' ); ?>>
					<?php do_action( 'woocommerce_register_form_start' ); ?>

					<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
						<p class="bon__champ">
							<label class="bon__libelle" for="reg_username">Identifiant</label>
							<input type="text" class="bon__saisie woocommerce-Input input-text" name="username" id="reg_username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" required aria-required="true"><?php // phpcs:ignore WordPress.Security.NonceVerification ?>
						</p>
					<?php endif; ?>

					<p class="bon__champ">
						<label class="bon__libelle" for="reg_email">E-mail</label>
						<input type="email" class="bon__saisie woocommerce-Input input-text" name="email" id="reg_email" autocomplete="email" value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" required aria-required="true"><?php // phpcs:ignore WordPress.Security.NonceVerification ?>
					</p>

					<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
						<p class="bon__champ">
							<label class="bon__libelle" for="reg_password">Mot de passe</label>
							<input type="password" class="bon__saisie woocommerce-Input input-text" name="password" id="reg_password" autocomplete="new-password" required aria-required="true">
						</p>
					<?php else : ?>
						<p class="compte-acces__note">Un lien pour choisir votre mot de passe vous sera envoyé par e-mail.</p>
					<?php endif; ?>

					<?php do_action( 'woocommerce_register_form' ); ?>

					<p class="compte-acces__action">
						<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
						<button type="submit" class="button compte__bouton compte__bouton--secondaire woocommerce-form-register__submit" name="register" value="Créer mon compte">Créer mon compte</button>
					</p>

					<?php do_action( 'woocommerce_register_form_end' ); ?>
				</form>
			</section>
		<?php endif; ?>
	</div>

	<ul class="compte-acces__avantages">
		<li>Suivi de vos commandes</li>
		<li>Adresses enregistrées</li>
		<li>Commande en une minute</li>
	</ul>
</div>
<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
