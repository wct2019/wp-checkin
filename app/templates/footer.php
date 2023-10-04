</div><!-- //.container -->

<footer class="navbar navbar-light bg-light">
	<div class="container d-block">
		<p class="text-center">
			<a href="https://github.com/wct2023/wp-checkin"><i class="fab fa-github"></i></a>
		</p>

		<p class="text-center">
			<span class="navbar-text">
				&copy; 2023 <a href="<?php echo getenv('WORDCAMP_OFFICIAL_SITE_URL'); ?>"><?php echo getenv('WORDCAMP_NAME'); ?></a>
			</span>
		</p>
	</div>

</footer>

<script src="/assets/js/app.js?s=<?= filemtime( dirname( __DIR__ ) . '/public/assets/js/app.js' ) ?>" type="application/javascript"></script>
</body>
</html>
