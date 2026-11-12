/**
 * „Mehr laden“ unter Archivlisten.
 *
 * Die Liste blätterte in Seiten zu je 20. Auf dem Handy heißt Blättern: ans
 * Ende scrollen, einen kleinen Link treffen, eine neue Seite laden, wieder
 * von oben anfangen. Der Knopf hängt die nächste Seite an die Liste an. Die
 * Blätterlinks bleiben im Markup — ohne JavaScript und für Suchmaschinen
 * funktioniert die Seite wie bisher.
 */
(function () {
	'use strict';

	var nav = document.querySelector('.mmm-l-pagination');
	var list = document.querySelector('#primary main.site-main');
	if (!nav || !list || !window.fetch || !window.DOMParser) return;

	var next = nav.querySelector('a.next');
	if (!next) return;

	var labels = window.mmmTheme || {};
	var url = next.href;

	var wrap = document.createElement('div');
	wrap.className = 'mmm-load-more';
	var btn = document.createElement('button');
	btn.type = 'button';
	btn.className = 'mmm-btn mmm-btn--ghost mmm-load-more__btn';
	btn.textContent = labels.more || 'Mehr';
	wrap.appendChild(btn);

	nav.hidden = true;
	nav.parentNode.insertBefore(wrap, nav);

	btn.addEventListener('click', function () {
		if (!url) return;

		btn.disabled = true;
		btn.classList.add('is-busy');
		btn.textContent = labels.loading || '…';

		fetch(url, { credentials: 'same-origin' })
			.then(function (r) {
				if (!r.ok) throw new Error(String(r.status));
				return r.text();
			})
			.then(function (html) {
				var doc = new DOMParser().parseFromString(html, 'text/html');
				var items = doc.querySelectorAll('#primary main.site-main > *');
				var first = null;

				Array.prototype.forEach.call(items, function (item) {
					var node = document.importNode(item, true);
					list.appendChild(node);
					if (!first) first = node;
				});

				var more = doc.querySelector('.mmm-l-pagination a.next');
				url = more ? more.href : '';

				if (url) {
					btn.disabled = false;
					btn.classList.remove('is-busy');
					btn.textContent = labels.more || 'Mehr';
				} else {
					wrap.remove();
				}

				// Der Fokus geht zum ersten neuen Eintrag, nicht zurück an den Seitenanfang.
				var link = first && first.querySelector('a');
				if (link) link.focus({ preventScroll: true });
			})
			.catch(function () {
				// Geht das Nachladen nicht, führt der Knopf wie ein Link weiter.
				window.location.href = url;
			});
	});
}());
