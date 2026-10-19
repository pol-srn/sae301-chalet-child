<?php
/**
 * E-mails du Chalet : pied bleu nuit (taureau, adresse, horaires, réseaux, texte du back-office).
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package chalet-child
 * @version 10.4.0
 */

defined( 'ABSPATH' ) || exit;

$email = $email ?? null;

$email_footer_text = get_option( 'woocommerce_email_footer_text' );
if ( apply_filters( 'woocommerce_is_email_preview', false ) ) { // phpcs:ignore WooCommerce.Commenting.CommentHooks -- Documenté dans email-styles.php.
	$text_transient    = get_transient( 'woocommerce_email_footer_text' );
	$email_footer_text = false !== $text_transient ? $text_transient : $email_footer_text;
}
$taureau = get_stylesheet_directory_uri() . '/assets/img/logo-taureau.png';
?>
																		</div>
																	</td>
																</tr>
															</table>
															<!-- End Content -->
														</td>
													</tr>
												</table>
												<!-- End Body -->
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td align="center" valign="top">
									<!-- Footer -->
									<table border="0" cellpadding="10" cellspacing="0" width="100%" id="template_footer" role="presentation">
										<tr>
											<td valign="top">
												<table border="0" cellpadding="10" cellspacing="0" width="100%" role="presentation">
													<tr>
														<td colspan="2" valign="middle" id="credit">
															<p class="pied-mail__logo"><img src="<?php echo esc_url( $taureau ); ?>" alt="Le Chalet" width="60" height="55"></p>
															<p class="pied-mail__infos"><?php echo esc_html( chalet_adresse_retrait() ); ?><br>05 55 77 51 81 · <?php echo esc_html( chalet_horaires_retrait() ); ?></p>
															<p class="pied-mail__reseaux">
																<a href="https://www.instagram.com/lechalet__restaurant/">Instagram</a> · <a href="https://www.facebook.com/LeChalet87">Facebook</a>
															</p>
															<?php
															echo wp_kses_post(
																wpautop(
																	wptexturize(
																		apply_filters( 'woocommerce_email_footer_text', $email_footer_text, $email ) // phpcs:ignore WooCommerce.Commenting.CommentHooks -- Hook de WooCommerce.
																	)
																)
															);
															?>
														</td>
													</tr>
												</table>
											</td>
										</tr>
									</table>
									<!-- End Footer -->
								</td>
							</tr>
						</table>
					</div>
				</td>
				<td><!-- Deliberately empty to support consistent sizing and layout across multiple email clients. --></td>
			</tr>
		</table>
	</body>
</html>
