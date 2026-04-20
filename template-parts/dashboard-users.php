<?php
/**
 * Dashboard — Brugere (admin-only).
 *
 * Liste over eksisterende brugere, opret-formular, og inline
 * redigering af dashboard-tilladelser + rolle. Bruges sammen med
 * POST-handleren øverst i page-dashboard.php.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! current_user_can( 'manage_options' ) ) {
	echo '<div class="sd-empty"><p>' . esc_html__( 'Kun administratorer har adgang til denne sektion.', 'studie247' ) . '</p></div>';
	return;
}

$sections  = studie247_dash_sections();
$sec_keys  = array_keys( $sections );
$all_users = get_users( array(
	'orderby' => 'display_name',
	'order'   => 'ASC',
	'number'  => -1,
) );

$edit_uid = isset( $_GET['edit_user'] ) ? (int) $_GET['edit_user'] : 0;

$err     = isset( $_GET['err'] ) ? sanitize_key( $_GET['err'] ) : '';
$saved   = ! empty( $_GET['saved'] );
$deleted = ! empty( $_GET['deleted'] );
$created = isset( $_GET['created'] ) ? (int) $_GET['created'] : 0;

$current_uid = get_current_user_id();
?>

<?php if ( $err || $saved || $deleted || $created ) : ?>
	<div class="sd-notice <?php echo $err ? 'sd-notice--error' : 'sd-notice--success'; ?>">
		<?php
		if ( 'exists' === $err ) {
			esc_html_e( 'En bruger med den e-mail findes allerede.', 'studie247' );
		} elseif ( 'create' === $err ) {
			esc_html_e( 'Kunne ikke oprette brugeren. Tjek e-mail og prøv igen.', 'studie247' );
		} elseif ( $created ) {
			$cu = get_userdata( $created );
			echo esc_html( sprintf( __( 'Brugeren %s er oprettet.', 'studie247' ), $cu ? $cu->display_name : '' ) );
		} elseif ( $deleted ) {
			esc_html_e( 'Brugeren er slettet.', 'studie247' );
		} elseif ( $saved ) {
			esc_html_e( 'Ændringer gemt.', 'studie247' );
		}
		?>
	</div>
<?php endif; ?>

<section class="sd-panel sd-users-create">
	<header class="sd-panel__head">
		<h2><?php esc_html_e( 'Opret ny bruger', 'studie247' ); ?></h2>
		<span class="sd-panel__hint"><?php esc_html_e( 'Brugeren får en tilfældig adgangskode — send gerne velkomstmailen.', 'studie247' ); ?></span>
	</header>

	<form class="sd-form sd-users-form" method="post" action="<?php echo esc_url( home_url( '/dashboard/?view=users' ) ); ?>">
		<?php wp_nonce_field( 's247_dash_users' ); ?>
		<input type="hidden" name="s247_dash_user_action" value="create">

		<div class="sd-form__row">
			<div class="sd-field">
				<label for="s247_new_first"><?php esc_html_e( 'Fornavn', 'studie247' ); ?></label>
				<input type="text" id="s247_new_first" name="new_first_name" required>
			</div>
			<div class="sd-field">
				<label for="s247_new_last"><?php esc_html_e( 'Efternavn', 'studie247' ); ?></label>
				<input type="text" id="s247_new_last" name="new_last_name">
			</div>
			<div class="sd-field">
				<label for="s247_new_email"><?php esc_html_e( 'E-mail', 'studie247' ); ?></label>
				<input type="email" id="s247_new_email" name="new_email" required>
			</div>
			<div class="sd-field">
				<label for="s247_new_role"><?php esc_html_e( 'Rolle', 'studie247' ); ?></label>
				<select id="s247_new_role" name="new_role">
					<option value="editor"><?php esc_html_e( 'Redaktør', 'studie247' ); ?></option>
					<option value="administrator"><?php esc_html_e( 'Administrator', 'studie247' ); ?></option>
				</select>
			</div>
		</div>

		<div class="sd-field">
			<span class="sd-field__label"><?php esc_html_e( 'Dashboard-adgang', 'studie247' ); ?></span>
			<div class="sd-checkboxes">
				<?php foreach ( $sections as $key => $label ) : ?>
					<label class="sd-checkbox">
						<input type="checkbox" name="perms[]" value="<?php echo esc_attr( $key ); ?>">
						<span><?php echo esc_html( $label ); ?></span>
					</label>
				<?php endforeach; ?>
			</div>
			<p class="sd-hint"><?php esc_html_e( 'Administratorer ser alt uanset flueben.', 'studie247' ); ?></p>
		</div>

		<div class="sd-field">
			<label class="sd-checkbox">
				<input type="checkbox" name="new_send_mail" value="1" checked>
				<span><?php esc_html_e( 'Send velkomstmail med login-oplysninger', 'studie247' ); ?></span>
			</label>
		</div>

		<div class="sd-form__actions">
			<button type="submit" class="sd-btn"><?php esc_html_e( 'Opret bruger', 'studie247' ); ?></button>
		</div>
	</form>
</section>

<section class="sd-panel sd-users-list">
	<header class="sd-panel__head">
		<h2><?php esc_html_e( 'Eksisterende brugere', 'studie247' ); ?></h2>
		<span class="sd-panel__hint"><?php echo esc_html( sprintf( _n( '%d bruger', '%d brugere', count( $all_users ), 'studie247' ), count( $all_users ) ) ); ?></span>
	</header>

	<table class="sd-table sd-users-table">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Navn', 'studie247' ); ?></th>
				<th><?php esc_html_e( 'E-mail', 'studie247' ); ?></th>
				<th><?php esc_html_e( 'Rolle', 'studie247' ); ?></th>
				<th><?php esc_html_e( 'Adgang', 'studie247' ); ?></th>
				<th class="sd-table__right"></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $all_users as $u ) :
				$uid      = (int) $u->ID;
				$is_admin = user_can( $u, 'manage_options' );
				$is_self  = $uid === $current_uid;
				$role     = $is_admin ? __( 'Administrator', 'studie247' ) : ( user_can( $u, 'edit_posts' ) ? __( 'Redaktør', 'studie247' ) : __( 'Andet', 'studie247' ) );
				$perm_labels = array();
				foreach ( $sec_keys as $k ) {
					if ( $is_admin ) { continue; }
					if ( '1' === get_user_meta( $uid, '_s247_dash_view_' . $k, true ) ) {
						$perm_labels[] = $sections[ $k ];
					}
				}
				$initial = strtoupper( substr( $u->display_name ?: $u->user_email, 0, 1 ) );
				$is_open = $edit_uid === $uid;
			?>
				<tr class="sd-users-row <?php echo $is_open ? 'is-open' : ''; ?>">
					<td>
						<div class="sd-table__name">
							<span class="sd-audit__avatar"><?php echo esc_html( $initial ); ?></span>
							<div>
								<div class="sd-list__name"><?php echo esc_html( $u->display_name ?: $u->user_login ); ?><?php if ( $is_self ) : ?> <span class="sd-tag"><?php esc_html_e( 'Dig', 'studie247' ); ?></span><?php endif; ?></div>
								<div class="sd-list__meta"><?php echo esc_html( sprintf( __( 'Medlem siden %s', 'studie247' ), date_i18n( 'j. M Y', strtotime( $u->user_registered ) ) ) ); ?></div>
							</div>
						</div>
					</td>
					<td><?php echo esc_html( $u->user_email ); ?></td>
					<td>
						<span class="sd-badge <?php echo $is_admin ? 'sd-badge--accent' : ''; ?>"><?php echo esc_html( $role ); ?></span>
					</td>
					<td>
						<?php if ( $is_admin ) : ?>
							<span class="sd-muted"><?php esc_html_e( 'Al adgang', 'studie247' ); ?></span>
						<?php elseif ( empty( $perm_labels ) ) : ?>
							<span class="sd-muted"><?php esc_html_e( 'Ingen', 'studie247' ); ?></span>
						<?php else : ?>
							<span><?php echo esc_html( implode( ', ', $perm_labels ) ); ?></span>
						<?php endif; ?>
					</td>
					<td class="sd-table__right">
						<?php if ( $is_open ) : ?>
							<a class="sd-btn sd-btn--ghost" href="<?php echo esc_url( home_url( '/dashboard/?view=users' ) ); ?>"><?php esc_html_e( 'Luk', 'studie247' ); ?></a>
						<?php else : ?>
							<a class="sd-btn sd-btn--ghost" href="<?php echo esc_url( home_url( '/dashboard/?view=users&edit_user=' . $uid ) ); ?>"><?php esc_html_e( 'Redigér', 'studie247' ); ?></a>
						<?php endif; ?>
					</td>
				</tr>
				<?php if ( $is_open ) : ?>
					<tr class="sd-users-edit">
						<td colspan="5">
							<form class="sd-form" method="post" action="<?php echo esc_url( home_url( '/dashboard/?view=users' ) ); ?>">
								<?php wp_nonce_field( 's247_dash_users' ); ?>
								<input type="hidden" name="s247_dash_user_action" value="update">
								<input type="hidden" name="user_id" value="<?php echo esc_attr( $uid ); ?>">

								<div class="sd-form__row">
									<div class="sd-field">
										<label for="s247_role_<?php echo esc_attr( $uid ); ?>"><?php esc_html_e( 'Rolle', 'studie247' ); ?></label>
										<select id="s247_role_<?php echo esc_attr( $uid ); ?>" name="role" <?php disabled( $is_self ); ?>>
											<option value="editor" <?php selected( ! $is_admin ); ?>><?php esc_html_e( 'Redaktør', 'studie247' ); ?></option>
											<option value="administrator" <?php selected( $is_admin ); ?>><?php esc_html_e( 'Administrator', 'studie247' ); ?></option>
										</select>
										<?php if ( $is_self ) : ?>
											<p class="sd-hint"><?php esc_html_e( 'Du kan ikke ændre din egen rolle.', 'studie247' ); ?></p>
										<?php endif; ?>
									</div>
								</div>

								<div class="sd-field">
									<span class="sd-field__label"><?php esc_html_e( 'Dashboard-sektioner', 'studie247' ); ?></span>
									<div class="sd-checkboxes">
										<?php foreach ( $sections as $key => $label ) :
											$checked = '1' === get_user_meta( $uid, '_s247_dash_view_' . $key, true );
										?>
											<label class="sd-checkbox">
												<input type="checkbox" name="perms[]" value="<?php echo esc_attr( $key ); ?>" <?php checked( $checked ); ?> <?php disabled( $is_admin ); ?>>
												<span><?php echo esc_html( $label ); ?></span>
											</label>
										<?php endforeach; ?>
									</div>
									<?php if ( $is_admin ) : ?>
										<p class="sd-hint"><?php esc_html_e( 'Administratorer ser alt — fluebenene har ingen effekt.', 'studie247' ); ?></p>
									<?php endif; ?>
								</div>

								<div class="sd-form__actions">
									<button type="submit" class="sd-btn"><?php esc_html_e( 'Gem', 'studie247' ); ?></button>
									<a class="sd-btn sd-btn--ghost" href="<?php echo esc_url( home_url( '/dashboard/?view=users' ) ); ?>"><?php esc_html_e( 'Annullér', 'studie247' ); ?></a>
								</div>
							</form>

							<?php if ( ! $is_self ) : ?>
								<form class="sd-users-delete" method="post" action="<?php echo esc_url( home_url( '/dashboard/?view=users' ) ); ?>" onsubmit="return confirm('<?php echo esc_js( sprintf( __( 'Slet brugeren %s? Handlingen kan ikke fortrydes.', 'studie247' ), $u->display_name ) ); ?>');">
									<?php wp_nonce_field( 's247_dash_users' ); ?>
									<input type="hidden" name="s247_dash_user_action" value="delete">
									<input type="hidden" name="user_id" value="<?php echo esc_attr( $uid ); ?>">
									<button type="submit" class="sd-btn sd-btn--danger"><?php esc_html_e( 'Slet bruger', 'studie247' ); ?></button>
								</form>
							<?php endif; ?>
						</td>
					</tr>
				<?php endif; ?>
			<?php endforeach; ?>
		</tbody>
	</table>
</section>
