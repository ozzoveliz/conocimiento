import '@styles/iframe';

$(document).ready(() => {

	function receiveMessage(event) {
		const timelyLoader = document.getElementById('timely-loader');
		const timelyExternal = event.data.timely_external;
		const timelyData = timelyExternal.data;
		let authToken = null;
		let expiryDate = null;
		if (timelyData) {
			authToken = timelyData['X-Auth-Token'];
			expiryDate = timelyData.expiryDate;
		}
		const { action } = timelyExternal;

		const data = {
			action: 'auth_token',
			auth_token_nonce: ajax_object.auth_token_nonce,
			timely_token: authToken,
			expiry_date: expiryDate,
			timely_action: action,
		};

		timelyLoader.style.zIndex = 9999;

		jQuery.post(ajax_object.xhr_url, data, (response) => {
			if (response !== 'logged out') {
				document.getElementById('timely-iframe').style.display =
					'none';
				location.reload();
			} else {
				timelyLoader.style.zIndex = 0;
			}
		});
	}

	function getTimelyURLFromWPUrl(currentUrl) {
		const submenuTimelyName = currentUrl.substring(
			currentUrl.indexOf('page=') + 'page='.length
		);
		const url = $('#'.concat(submenuTimelyName)).val();
		return url ? url : '/';
	}

	function getValidQueryParam(pathName) {
		const fragmentMatcher = /^\/([a-z0-9-_]+(\/[a-z0-9-_]+)?)?$/gm;
		if (fragmentMatcher.test(pathName)) {
			return pathName;
		} else {
			return '/';
		}
	}

	let iframeContainerStatus;
	const iframe = document.createElement('iframe');
	const iframeContainer = document.getElementById(
		'timely-iframe-container'
	);

	if (iframeContainer) {

		const initialUrl = getTimelyURLFromWPUrl(window.location.href);

		if (iframeContainerStatus === 'loaded') {
			const parsedUrl = new URL(initialUrl);
			iframe.contentWindow.postMessage({
				source: 'wp',
				action: 'navigate',
				route: getValidQueryParam(parsedUrl.pathname)
			}, '*');
		} else {
			iframeContainerStatus = 'loading';
			iframe.id = 'timely-iframe';
			iframe.src = initialUrl;
			iframe.style.display = 'none';
			iframe.onload = () => {
				iframe.style.display = 'block';
				iframeContainerStatus = 'loaded';
			};
		}

		iframeContainer.appendChild(iframe);

		/* adjust height */
		const pageHeight = $(document).height();
		const iframeHeight = $(iframe).height();
		if (iframeHeight + 32 < pageHeight) {
			$(iframe).height(pageHeight);
			$('#timely-iframe-container').height(pageHeight);
		}

		window.addEventListener('message', receiveMessage, false);

		/* links reload iframe */
		const submenuTimely = $("a[href*='page=timely']")
			.not("a[href*='#timely']"); // ignore the timely_user_guide option
		submenuTimely.each((index) => {
			const element = submenuTimely[index];
			const elHref = element.href;

			element.addEventListener('click', (e) => {
				if (iframeContainerStatus === 'loading') {
					e.preventDefault();
					return;
				}
				const src = getTimelyURLFromWPUrl(elHref);
				if (iframeContainerStatus === 'loaded') {
					const parsedUrl = new URL(src);
					iframe.contentWindow.postMessage({
						source: 'wp',
						action: 'navigate',
						route: getValidQueryParam(parsedUrl.pathname)
					}, '*');
					window.history.pushState(null, "Time.ly", elHref);
				} else {
					iframeContainerStatus = 'loading';
					const timelyLoader = document.getElementById('timely-loader');
					timelyLoader.style.zIndex = 9999;
					iframe.src = src;
					iframe.onload = () => {
						iframeContainerStatus === 'loaded';
						setTimeout(() => {
							timelyLoader.style.zIndex = 0;
						}, 1000);
					};
				}

				$('#toplevel_page_timely>ul>li.current').removeClass('current');
				e.target.parentNode.classList.add('current');

				e.preventDefault();
			});
		});
	}

	/* open links in another tab */
	const submenuTimelyTarget = $("ul.wp-submenu a[href*='#timely']");
	submenuTimelyTarget.attr('target', '_blank');
	submenuTimelyTarget.each((index) => {
		const element = submenuTimelyTarget[index];
		const elHref = element.href;
		element.href = 'https://'.concat(
			elHref.substring(elHref.indexOf('#timely=') + '#timely='.length)
		);
	});

	const updateMessageButton = document.getElementById('timely-update-message-button');
	if (updateMessageButton) {
		updateMessageButton.addEventListener(
			'click',
			() => {
				document.getElementById('timely-update-message').style.display = 'none';
			}
		);
	}
});
