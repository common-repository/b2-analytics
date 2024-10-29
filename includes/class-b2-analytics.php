<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://b2.ai
 * @since      1.0.0
 *
 * @package    B2_Analytics
 * @subpackage B2_Analytics/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    B2_Analytics
 * @subpackage B2_Analytics/includes
 * @author     B2 <info@b2.ai>
 */
class B2_Analytics {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      B2_Analytics_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'B2_ANALYTICS_VERSION' ) ) {
			$this->version = B2_ANALYTICS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'b2-analytics';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

		add_action('admin_menu', array($this, 'create_plugin_settings_page'));
		add_action('admin_init', array($this, 'add_css_js'));	

	}

	public function create_plugin_settings_page() {
        $page_title = 'B2 Ad Block Analytics';
        $menu_title = 'B2 Analytics';
        $capability = 'manage_options';
        $slug = 'b2-analytics';
        $callback = array($this, 'plugin_settings_page_content');
        $icon = 'dashicons-chart-area';
        $position = 50;
        add_menu_page($page_title, $menu_title, $capability, $slug, $callback, $icon, $position);
		
    }

	public function add_css_js() {	
		wp_enqueue_style( 'bootstrap', plugins_url('/css/bootstrap/bootstrap.min.css', __FILE__), false, '5.3.0', 'all');
        wp_enqueue_style( 'b2', plugins_url('/css/app.css', __FILE__), false, '1.0.0', 'all');
		wp_enqueue_script( 'apexcharts', plugins_url('/js/apexcharts.min.js', __FILE__), array( 'jquery' ), '3.35.1', true );
		wp_enqueue_script( 'popper', plugins_url('/js/popper.min.js', __FILE__), array( 'jquery' ), '1.16.0', true );
		wp_enqueue_script( 'bootstrap-js', plugins_url('/js/bootstrap.min.js', __FILE__), array( 'jquery' ), '5.3.0', true );
		wp_enqueue_script( 'd3-js', plugins_url('/js/d3.min.js', __FILE__), array( 'jquery' ), '4.18', true );
		wp_enqueue_script( 'd3-geo-projection', plugins_url('/js/d3-geo-projection.v2.min.js', __FILE__), array( 'jquery' ), '2.0.0', true );
		wp_enqueue_script( 'd3-scale-chromatic', plugins_url('/js/d3-scale-chromatic.v1.min.js', __FILE__), array( 'jquery' ), '1.0.0', true );
    }

	
    public function plugin_settings_page_content() { 

		add_action('wp_enqueue_scripts', 'my_plugin_scripts' );
		add_action('admin_enqueue_scripts', 'add_css_js');
		
		// Get the raw customerID value from the WordPress option and trim whitespace
		$rawCustomerID = trim(get_option(B2_Analytics::get_option_name("CustomerID")), "");

		// Sanitize the customerID value using sanitize_text_field()
		$sanitizedCustomerID = sanitize_text_field($rawCustomerID);

		// Escape the sanitized customerID value using esc_html()
		$escapedCustomerID = esc_html($sanitizedCustomerID);

		// Get the raw authKey value from the WordPress option and trim whitespace
		$rawAuthKey = trim(get_option(B2_Analytics::get_option_name("AuthKey")), "");

		// Sanitize the authKey value using sanitize_text_field()
		$sanitizedAuthKey = sanitize_text_field($rawAuthKey);

		// Escape the sanitized authKey value using esc_html()
		$escapedAuthKey = esc_html($sanitizedAuthKey);

		// Validate the escaped customerID and authKey values
		if (!ctype_alnum($escapedCustomerID) || !ctype_alnum($escapedAuthKey)) {
			// Handle invalid customerID or authKey error here
		}

		// Assign the validated escaped customerID and authKey values to variables
		$customerID = $escapedCustomerID;
		$authKey = $escapedAuthKey;
		

		// Sanitize the $_SERVER["SERVER_NAME"] variable using sanitize_text_field()
		$serverName = sanitize_text_field($_SERVER["SERVER_NAME"]);

		// Escape the sanitized $serverName variable using esc_html()
		$escapedServerName = esc_html($serverName);

		// Validate the escaped $escapedServerName variable to ensure it's a valid domain name
		if (filter_var($escapedServerName, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) === false) {
			// Handle invalid domain name error here
		}

		// Assign the validated $escapedServerName variable to $domain
		$domain = $escapedServerName;

		$url = "https://portal.b2.ai/api/Plugin/authenticate";
		$headers = array(
			'accept' => 'text/plain',
			'Content-Type' => 'application/json'
		);

		$data = array(
			'domain' => $domain,
			'authToken' => $authKey,
			'customerId' => $customerID
		);

		$args = array(
			'headers' => $headers,
			'body' => json_encode($data),
			'method' => 'POST',
			'timeout' => 45
		);

		$response = wp_remote_post($url, $args);
		$httpcode = wp_remote_retrieve_response_code($response);

		if($httpcode == 200)
		{
			$bearerToken = json_decode(wp_remote_retrieve_body($response))->data;
			//add_option((B2_Analytics::get_option_name("BearerToken")), $bearerToken); 
		}
		else
		{
			// Error getting bearer token... 
			echo "Error: " . esc_html($httpcode) . "<br/>";
			echo "Unable to get bearer token...";
			echo esc_html(wp_remote_retrieve_body($response));	

			return;
		}
		

		$url = 'https://portal.b2.ai/api/Plugin/Dashboard';

		$dashboardData = "";
		$geoData = "";

		$request_headers = array(
			'Authorization' => 'Bearer ' . $bearerToken
		);
		
		$response = wp_remote_get( $url, array(
			'headers' => $request_headers
		) );

		$httpcode = wp_remote_retrieve_response_code( $response );
			
		if($httpcode == 200)
		{

			$dashboardData = json_decode(wp_remote_retrieve_body($response));
			echo '<script>var dashboardData = ' . wp_kses_post(json_encode($dashboardData)) . ';</script>';
			
			$json_location = plugins_url('/js/custom.geo.json', __FILE__);
			echo '<script>var jsonLocation = "' . esc_url($json_location) . '";</script>';

			?>
			<script type="module">
				import { drawMap } from "<?php echo esc_js(plugins_url('/js/WorldChart.js', __FILE__)); ?>";
				window.drawMap = drawMap;
			</script>
			<script> 
			
				var allowedAds = [];
				var blockedAds = [];
				var allowedMobileAds = [];
				var blockedMobileAds = [];
				var allowedDesktopAds = [];
				var blockedDesktopAds = [];
				var allowedTabletAds = []
				var blockedTabletAds = [];
				var totalPageViews = 0;
				var totalUniqueUsers = 0;
				var totalAllowedAds = 0;
				var totalBlockedAds = 0;
				var totalMobileAllowedAds = 0;
				var totalMobileBlockedAds = 0;
				var totalTabletAllowedAds = 0;
				var totalTabletBlockedAds = 0;
				var totalDesktopAllowedAds = 0;
				var totalDesktopBlockedAds = 0;
				var domainName = dashboardData.data.aggregatedDataList[0].domain;

				for(x of dashboardData.data.aggregatedDataList)
				{
					var jstime =  new Date(x.startDateTime.substring(0,10)).getTime();

					totalPageViews += x.totalPageViews;
					totalUniqueUsers += x.uniqueUserCount;
					totalAllowedAds += x.totalNoAdBlockCount;
					totalBlockedAds += x.totalDomainAdBlockerCount + x.totalCssAdBlockerCount + x.totalDomainCssAdBlockerCount;
					totalMobileBlockedAds += x.mobileAdBlockCount;
					totalMobileAllowedAds += x.mobileCount - x.mobileAdBlockCount;
					totalTabletBlockedAds += x.tabletAdBlockCount;
					totalTabletAllowedAds += x.tabletCount - x.tabletAdBlockCount;
					totalDesktopBlockedAds += x.desktopAdBlockCount;
					totalDesktopAllowedAds += x.desktopCount - x.desktopAdBlockCount;

					if(allowedAds.length == 0 || allowedAds[allowedAds.length-1][0] < jstime)
					{
						let values = [jstime, x.totalNoAdBlockCount];
						allowedAds.push(values);
					}
					else
					{
						allowedAds[allowedAds.length-1][1] += x.totalNoAdBlockCount;
					}

					if(blockedAds.length == 0 || blockedAds[blockedAds.length-1][0] < jstime)
					{
						let values = [jstime, x.totalDomainAdBlockerCount + x.totalCssAdBlockerCount + x.totalDomainCssAdBlockerCount];
						blockedAds.push(values);
					}
					else
					{
						blockedAds[blockedAds.length-1][1] += x.totalDomainAdBlockerCount + x.totalCssAdBlockerCount + x.totalDomainCssAdBlockerCount;
					}

					if(allowedMobileAds.length == 0 || allowedMobileAds[allowedMobileAds.length-1][0] < jstime)
					{
						let values = [jstime, x.mobileCount - x.mobileAdBlockCount];
						allowedMobileAds.push(values);						
					}
					else
					{
						allowedMobileAds[allowedMobileAds.length-1][1] += x.mobileCount - x.mobileAdBlockCount;
					}

					if(blockedMobileAds.length == 0 || blockedMobileAds[blockedMobileAds.length-1][0] < jstime)
					{
						let values = [jstime, x.mobileAdBlockCount];
						blockedMobileAds.push(values);
					}
					else
					{
						blockedMobileAds[blockedMobileAds.length-1][1] += x.mobileAdBlockCount;
					}

					if(allowedDesktopAds.length == 0 || allowedDesktopAds[allowedDesktopAds.length-1][0] < jstime)
					{
						let values = [jstime, x.desktopCount - x.desktopAdBlockCount];
						allowedDesktopAds.push(values);
					}
					else
					{
						allowedDesktopAds[allowedDesktopAds.length-1][1] += x.desktopCount - x.desktopAdBlockCount;
					}

					if(blockedDesktopAds.length == 0 || blockedDesktopAds[blockedDesktopAds.length-1][0] < jstime)
					{
						let values = [jstime, x.desktopAdBlockCount];
						blockedDesktopAds.push(values);
					}
					else
					{
						blockedDesktopAds[blockedDesktopAds.length-1][1] += x.desktopAdBlockCount;
					}

					if(allowedTabletAds.length == 0 || allowedTabletAds[allowedTabletAds.length-1][0] < jstime)
					{
						let values = [jstime, x.tabletCount - x.tabletAdBlockCount];
						allowedTabletAds.push(values);
					}
					else
					{
						allowedTabletAds[allowedTabletAds.length-1][1] += x.tabletCount - x.tabletAdBlockCount;
					}

					if(blockedTabletAds.length == 0 || blockedTabletAds[blockedTabletAds.length-1][0] < jstime)
					{
						let values = [jstime, x.tabletAdBlockCount];
						blockedTabletAds.push(values);
					}
					else
					{
						blockedTabletAds[blockedTabletAds.length-1][1] += x.tabletAdBlockCount;
					}

				}


				var chartOptions = {
					series : [
						{							
							name: "Allowed Ads", 
							data: allowedAds
						},
						{
							name: "Blocked Ads",
							data: blockedAds
						}
					]
					,
					chart: {
					type: 'area',
					height: 400,
					stacked: true,
						events: {
							selection: function (chart, e) {
							console.log(new Date(e.xaxis.min))
							}
						},
					},
					colors: ['#32F16C', '#F13232'],
					dataLabels: {
					enabled: false
					},
					stroke: {
					curve: 'smooth'
					},
					fill: {
					type: 'gradient',
					gradient: {
						shadeIntensity: 1,
						opacityFrom: 0.2,
						opacityTo: 0.9,
					}
					},
					legend: {
					position: 'top',
					horizontalAlign: 'left'
					},
					xaxis: {
						type: 'datetime'
					}
				}

				var mobileChartOptions = {
					series : [
						{							
							name: "Allowed Ads on Mobile", 
							data: allowedMobileAds
						},
						{
							name: "Blocked Ads on Mobile",
							data: blockedMobileAds
						}
					]
					,
					chart: {
					type: 'area',
					height: 400,
					stacked: true,
						events: {
							selection: function (chart, e) {
							console.log(new Date(e.xaxis.min))
							}
						},
					},
					colors: ['#32F16C', '#F13232'],
					dataLabels: {
					enabled: false
					},
					stroke: {
					curve: 'smooth'
					},
					fill: {
					type: 'gradient',
					gradient: {
						shadeIntensity: 1,
						opacityFrom: 0.2,
						opacityTo: 0.9,
					}
					},
					legend: {
					position: 'top',
					horizontalAlign: 'left'
					},
					xaxis: {
					type: 'datetime'
					}
				}

				var desktopChartOptions = {
					series : [
						{							
							name: "Allowed Ads on Desktops", 
							data: allowedDesktopAds
						},
						{
							name: "Blocked Ads on Desktops",
							data: blockedDesktopAds
						}
					]
					,
					chart: {
					type: 'area',
					height: 400,
					stacked: true,
						events: {
							selection: function (chart, e) {
							console.log(new Date(e.xaxis.min))
							}
						},
					},
					colors: ['#32F16C', '#F13232'],
					dataLabels: {
					enabled: false
					},
					stroke: {
					curve: 'smooth'
					},
					fill: {
					type: 'gradient',
					gradient: {
						shadeIntensity: 1,
						opacityFrom: 0.2,
						opacityTo: 0.9,
					}
					},
					legend: {
					position: 'top',
					horizontalAlign: 'left'
					},
					xaxis: {
					type: 'datetime'
					}
				}

				var tabletChartOptions = {
					series : [
						{							
							name: "Allowed Ads on Tablets", 
							data: allowedDesktopAds
						},
						{
							name: "Blocked Ads on Tablets",
							data: blockedTabletAds
						}
					]
					,
					chart: {
					type: 'area',
					height: 400,
					stacked: true,
						events: {
							selection: function (chart, e) {
							console.log(new Date(e.xaxis.min))
							}
						},
					},
					colors: ['#32F16C', '#F13232'],
					dataLabels: {
					enabled: false
					},
					stroke: {
					curve: 'smooth'
					},
					fill: {
					type: 'gradient',
					gradient: {
						shadeIntensity: 1,
						opacityFrom: 0.2,
						opacityTo: 0.9,
					}
					},
					legend: {
					position: 'top',
					horizontalAlign: 'left'
					},
					xaxis: {
					type: 'datetime'
					}
				}

			

				function updateNumbers(pageViews, adBlocked, adAllowed)
				{	

					document.querySelector("#totalPageViews1").innerHTML = nFormatter(pageViews, 2);
					document.querySelector("#totalPageViews2").innerHTML = nFormatter(pageViews, 2);
					
					document.querySelector("#pageViewsAllowingAdsPercentage").innerHTML = (adAllowed / pageViews * 100).toFixed(2) + "%";
					document.querySelector("#pageViewsBlockingAdsPercentage").innerHTML = (adBlocked / pageViews * 100).toFixed(2) + "%";

					document.querySelector("#pageViewsAllowingAds").innerHTML = nFormatter(adAllowed, 1);
					document.querySelector("#pageViewsBlockingAds").innerHTML = nFormatter(adBlocked, 1);
				}

				window.addEventListener("load", () => {

					document.querySelector("#domainName").innerHTML = domainName;
					
					document.querySelector("#totalVisitors").innerHTML = nFormatter(totalUniqueUsers, 2);
					document.querySelector("#pagesPerVisit").innerHTML = (totalPageViews / totalUniqueUsers).toFixed(2);

					showAllChart();

					loadGeoCharts();

				});

				function showAllChart()
				{
					updateNumbers(totalPageViews, totalBlockedAds, totalAllowedAds);
					document.querySelector("#adBlockChart").innerHTML = "";
					chart = new ApexCharts(document.querySelector("#adBlockChart"), chartOptions);
       				chart.render();
				}

				function showDesktopChart()
				{
					updateNumbers(totalDesktopBlockedAds + totalDesktopAllowedAds, totalDesktopBlockedAds, totalDesktopAllowedAds);
					document.querySelector("#adBlockChart").innerHTML = "";
					chart = new ApexCharts(document.querySelector("#adBlockChart"), desktopChartOptions);
       				chart.render();
				}

				function showMobileChart()
				{
					updateNumbers(totalMobileBlockedAds + totalMobileAllowedAds, totalMobileBlockedAds, totalMobileAllowedAds);
					document.querySelector("#adBlockChart").innerHTML = "";
					chart = new ApexCharts(document.querySelector("#adBlockChart"), mobileChartOptions);
       				chart.render();
				}

				function showTabletChart()
				{
					updateNumbers(totalTabletBlockedAds + totalTabletAllowedAds, totalTabletBlockedAds, totalTabletAllowedAds);
					document.querySelector("#adBlockChart").innerHTML = "";
					chart = new ApexCharts(document.querySelector("#adBlockChart"), tabletChartOptions);
       				chart.render();
				}


				function nFormatter(num, digits) {
				var si = [
					{ value: 1, symbol: "" },
					{ value: 1E3, symbol: "k" },
					{ value: 1E6, symbol: "M" },
					{ value: 1E9, symbol: "G" },
					{ value: 1E12, symbol: "T" },
					{ value: 1E15, symbol: "P" },
					{ value: 1E18, symbol: "E" }
				];
				var rx = /\.0+$|(\.[0-9]*[1-9])0+$/;
				var i;
				for (i = si.length - 1; i > 0; i--) {
					if (num >= si[i].value) {
					break;
					}
				}
				return (num / si[i].value).toFixed(digits).replace(rx, "$1") + si[i].symbol;
				}

		
			</script>			
			<?php
			
		}
		else
		{
			// Error getting bearer token... 
			echo "Error: " . esc_html($httpcode) . "<br/>";
			echo "Failed...";	
			delete_option((B2_Analytics::get_option_name("BearerToken")), $bearerToken); 
		
			return;
		}
		
		echo "<div id='app'><div class='page'><main><article class='content px-4'><div class='col-12'>";
		echo '<h1>' . esc_html( 'B2 Ad Block Analytics' ) . '</h1>';

		// If no entries yet
		if(count($dashboardData->data->aggregatedDataList) == 0)
		{
			
		
		?>
			<div class="card d-flex flex-column bg-white p-3">
				<div class="d-flex flex-row">
					<div class="blob green mr-3 mt-2"></div>
					<h3 class="">Waiting for first page view</h3>
				</div>
				<div class="muted">It can take 5 - 10 minutes for the data to process if the plugin was just activated</div>
			</div>

			<div class="m-3" />
		<?php

		}
		else
		{

		?>	

	<div class="d-flex justify-content-between">
		<div class="d-flex justify-content-between">
			<div class="display-6 fw-bold" id="domainName"></div>
		</div>
		<div class="float-end">
            <button id="allBtn" type="button" onclick="showAllChart()" class="btn btn-outline-primary p-2 mr-3">All</button>
            <button id="desktopBtn" type="button" onclick="showDesktopChart()" class="btn btn-outline-primary p-2 mr-3"><span class="oi oi-monitor mr-3" aria-hidden="true"></span>Desktop</button>
            <button id="tabletBtn" type="button" onclick="showTabletChart()" class="btn btn-outline-primary p-2 mr-3"><span class="oi oi-tablet mr-3" aria-hidden="true"></span>Tablet</button>
            <button id="mobileBtn" type="button" onclick="showMobileChart()" class="btn btn-outline-primary p-2 mr-3"><span class="oi oi-phone mr-3" aria-hidden="true"></span>Mobile</button>
        </div>
		</div>
		


			<div class="d-flex mt-3 flex-md-column flex-lg-row">
                <div class="container card d-flex flex-column bg-white col-lg-4 col-md-12 mt-md-2 mr-2">
                    <div class="d-flex flex-column align-items-center p-3">
                        <div class="">Total Pageviews</div>
                        <div class="display-6 fw-bold" id="totalPageViews1"></div>
                    </div>
                    <table class="table table-striped">
                        <tr>
                            <td>Visitors</td>
                            <td class="text-end" id="totalVisitors"></td>
                        </tr>
                        <tr>
                            <td>Pageviews</td>
                            <td class="text-end" id="totalPageViews2"></td>
                        </tr>
                        <tr>
                            <td>Page Visits</td>
                            <td class="text-end" id="pagesPerVisit"></td>
                        </tr>
                    </table>
                </div>

                <div class="container card d-flex flex-column bg-white col-md-12 col-lg-4 mt-md-2 mr-2">
                    <div class="d-flex flex-column align-items-center p-3">
                        <div class="">Pageviews Allowing Ads</div>
                        <div class="display-6 text-success fw-bold" id="pageViewsAllowingAdsPercentage"></div>
                    </div>
                    <table class="table table-striped">

                        <tr>
                            <td>Pageviews</td>
                            <td class="text-end" id="pageViewsAllowingAds"></td>
                        </tr>

                    </table>
                </div>


                <div class="container card d-flex flex-column bg-white col-md-12 col-lg-4 mt-md-2">
                    <div class="d-flex flex-column align-items-center p-3">
                        <div class="">Pageviews Blocking Ads</div>
                        <div class="display-6 text-danger fw-bold" id="pageViewsBlockingAdsPercentage"></div>
                    </div>
                    <table class="table table-striped">

                        <tr>
                            <td>Pageviews</td>
                            <td class="text-end" id="pageViewsBlockingAds"></td>
                        </tr>

                    </table>
                </div>

            </div>


			<div class="card d-flex flex-column bg-white mt-3 col-12" style="margin: 0px; width: 100%; max-width: 100% !important;">
            	<div class="card-header bg-white fw-bold">Ad Blocking Summary</div>
            	<div class="card-body">
					<div>
                		<div id="adBlockChart"></div>
					</div>
				</div>
			</div>



		<?php

		}

		?>

				<script>

				var geoDataSummary = [];

				function renderGeoData()
				{
					drawMap(JSON.stringify(geoDataSummary));
				}

				function renderGeoSummaryTable()
				{

					var top10 = [...geoDataSummary].splice(0, 10);
					const tableData = top10.map(value => {
					return (
						`<tr>
							<td>${value.countryName}</td>
							<td>${value.cssAdBlockCount}</td>
							<td>${value.domainAdBlockCount}</td>
							<td>${value.domainCssAdBlockCount}</td>
							<td>${value.blockTotalCount}</td>
							<td>${value.noAdBlockCount}</td>
							<td>${value.totalPageViews}</td>
							<td>${value.uniqueUserCount}</td>
						</tr>`
					);
					}).join('');


					var others = [...geoDataSummary].splice(10, (geoDataSummary.length - 10));
					var otherRow = `<tr>
							<td>Others <span class="text-muted">(${others.length} countries)</span></td>
							<td>${others.reduce((n, {cssAdBlockCount}) => n + cssAdBlockCount, 0)}</td>
							<td>${others.reduce((n, {domainAdBlockCount}) => n + domainAdBlockCount, 0)}</td>
							<td>${others.reduce((n, {domainCssAdBlockCount}) => n + domainCssAdBlockCount, 0)}</td>
							<td>${others.reduce((n, {blockTotalCount}) => n + blockTotalCount, 0)}</td>
							<td>${others.reduce((n, {noAdBlockCount}) => n + noAdBlockCount, 0)}</td>
							<td>${others.reduce((n, {totalPageViews}) => n + totalPageViews, 0)}</td>
							<td>${others.reduce((n, {uniqueUserCount}) => n + uniqueUserCount, 0)}</td>
						</tr>`

					var totalRow = `<tr class="fw-bold">
						<td>Total</td>
						<td>${geoDataSummary.reduce((n, {cssAdBlockCount}) => n + cssAdBlockCount, 0)}</td>
						<td>${geoDataSummary.reduce((n, {domainAdBlockCount}) => n + domainAdBlockCount, 0)}</td>
						<td>${geoDataSummary.reduce((n, {domainCssAdBlockCount}) => n + domainCssAdBlockCount, 0)}</td>
						<td>${geoDataSummary.reduce((n, {blockTotalCount}) => n + blockTotalCount, 0)}</td>
						<td>${geoDataSummary.reduce((n, {noAdBlockCount}) => n + noAdBlockCount, 0)}</td>
						<td>${geoDataSummary.reduce((n, {totalPageViews}) => n + totalPageViews, 0)}</td>
						<td>${geoDataSummary.reduce((n, {uniqueUserCount}) => n + uniqueUserCount, 0)}</td>
					</tr>`

					const tableBody = document.querySelector("#geoSummaryTable");
					tableBody.innerHTML = tableData + otherRow + totalRow;
				}


				function renderGeoSummaryChart()
				{
					var topResults = geoDataSummary.slice(0, 5);
					var chartOptions = {
							series: [{
								name: "Allowed Ads",
								data: topResults.map(a => a.noAdBlockCount)
							}, {
								name: "Blocked Ads",
								data: topResults.map(a => a.blockTotalCount)
							}],
							dataLabels: {
								enabled: false,
							},
							chart: {
								type: 'bar',
								height: 350
							},
							colors:["#9bf8b7", "#f9a0a0" ],
							plotOptions: {
								bar: {
									horizontal: false,
								}
							},
							stroke: {
								show: true,
								width: 1,
								colors: ['#fff']
							},
							tooltip: {
								shared: true,
								intersect: false
							},
							xaxis: {
								categories: topResults.map(a => a.countryName),
							},
						};

					chart = new ApexCharts(document.querySelector("#geoSummaryChart"), chartOptions);
       				chart.render();
				}


				function loadGeoCharts()
				{

					jQuery.ajax({
						type: "GET",
						url: "https://portal.b2.ai/api/Plugin/geodatasummary?days=30&domain=<?php echo esc_js($domain);?>",
						headers: {
							Authorization: 'Bearer <?php echo esc_html($bearerToken); ?>'
						},
						dataType: 'json',
						success: function (result, status, xhr) {
							geoDataSummary = result.data;
							renderGeoData();
							renderGeoSummaryTable();
							renderGeoSummaryChart();
						},
						error: function (xhr, status, error) {
							console.log(error);
						}
					});			
					
					return;
				}

				</script>
				<div class="card d-flex flex-column bg-white mt-3 col-12" style="margin: 0px; width: 100%; max-width: 100% !important;">

				<div class="card-header bg-white fw-bold">Geographical Summary</div>
				<div class="card-body">

					<div class="d-flex flex-row col-12 flex-lg-row flex-md-column">
						<div class="col-md-12 col-lg-6">
							<svg id="d3Div" style="height: 370px; width: 650px"></svg>
						</div>
						<div class="col-lg-5 col-md-12">
							<!-- Bar chart --> 
							<div id="geoSummaryChart" />
						</div>
					</div>
					</div>


				<?php 
				// If no entries yet
				if(count($dashboardData->data->geoDataList) == 0)
				{		
				?>

						<div class="row col-12" style="position: absolute; margin-top: -300px;">
							<div class="card bg-white col-3 p-5 col-md-4 offset-md-4 rounded-3">
								Geographical data is processed every 6 hours. Please check back again in a few hours.
							</div>
						</div>
				<?php
				}
				?>
					<div class="col-12">
					<table class="table table-striped table-bordered p-2">
						<thead>
							<tr style="font-size: 12px">
								<th></th>
								<th class="border" colspan="4" style="text-align: center">Pageviews with Blockers</th>
								<th class="border" style="text-align: center">Pageviews with No Blockers</th>
								<th class="border" colspan="2" style="text-align: center">Traffic</th>
							</tr>
							<tr style="font-size: 12px">
								<th>Country</th>
								<th>CSS <br>Blockers</th>
								<th>Domain <br>Blockers</th>
								<th>CSS & <br>Domain Blockers</th>
								<th>Total</th>
								<th>No Ad Blockers</th>
								<th style="text-align: right">Total <br>Page Views</th>
								<th style="text-align: right">Unique <br>Users</th>
							</tr>
						</thead>
						<tbody id="geoSummaryTable">
						
						</tbody>
					</table>
					</div>
				</div>
				</div>

	<?php
		// close these: <div id='app'><div class='page'><main><article class='content px-4'>
		echo "</article></main></div></div>"; 
	}


	/**
	* The name of the plugin option given a key 
	*/
	public static function get_option_name($key) {
		return "_b2-analytics_" . $key;
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - B2_Analytics_Loader. Orchestrates the hooks of the plugin.
	 * - B2_Analytics_i18n. Defines internationalization functionality.
	 * - B2_Analytics_Admin. Defines all hooks for the admin area.
	 * - B2_Analytics_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-b2-analytics-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-b2-analytics-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-b2-analytics-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-b2-analytics-public.php';

		$this->loader = new B2_Analytics_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the B2_Analytics_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new B2_Analytics_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	
	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new B2_Analytics_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new B2_Analytics_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		$this->loader->add_action( 'wp_footer', $plugin_public, 'b2_script' );

	}



	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}


	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    B2_Analytics_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	
	// create GUID
	public static function createGUID()
	{
		$set_uuid = "";

		if (function_exists('com_create_guid'))
		{
			$set_uuid = com_create_guid();
		}
		else
		{
		mt_srand((double)microtime()*10000);
		//optional for php 4.2.0 and up.
		$set_charid = strtoupper(md5(uniqid(rand(), true)));
		$set_hyphen = chr(45);
		// "-"
		$set_uuid = chr(123)
		.substr($set_charid, 0, 8).$set_hyphen
		.substr($set_charid, 8, 4).$set_hyphen
		.substr($set_charid,12, 4).$set_hyphen
		.substr($set_charid,16, 4).$set_hyphen
		.substr($set_charid,20,12)
		.chr(125);
		// "}"
		}

		return trim($set_uuid,"{}");    
	}


}
