$(document).on("data.app", function () {
	$("#btn-import").prop("disabled", false);
});

$(function () {
	$("#analyzed").on(
		{
			click: clickOption,
		},
		"ul.dropdown-menu a"
	);
	$("#analyzed").on(
		{
			click: click_trash,
		},
		"a.glyphicon-trash"
	);
});

function click_trash(event) {
	$(this).closest("li.list-group-item").remove();
	updateStats();
}
function do_import() {
	$("#analyzed").empty();
	const content = $('textarea[name="content"]').val();
	const lines = content.split(/[\r\n]+/);

	for (let i = 0; i < lines.length; i++) {
		const cardRow = getCardRowForLine(lines[i], i);
		if (!cardRow) continue;
		$("#analyzed").append(
			`<li class="list-group-item">${cardRow}<a class="pull-right glyphicon glyphicon-trash"></a></li>`
		);
	}
	updateStats();
}
function getCardRowForLine(line, lineNumber) {
	const results = NRDB.fuzzy_search.lookup(line);
	if (!results || (results.cards.length === 0 && results.qty === null))
		return;

	const { cards, qty } = results;

	if (cards.length == 0) {
		return `<span class="card" href="#">No match for ${line}</span>`;
	} else if (cards.length == 1) {
		return `<a class="card" data-code="${cards[0].code}" data-qty="${qty}" href="#">${cards[0].title}</a>`;
	} else {
		return `
      ${qty}x <a class="card dropdown-toggle text-warning" data-toggle="dropdown" data-code="${
			cards[0].code
		}" href="#"><span class="title">${
			cards[0].title
		}</span> <span class="caret"></span></a>
      <ul class="dropdown-menu">
        ${cards
			.toSorted((a, b) => {
				// Keep cards with the same name as the first card at the top
				const aHasSameTitle = a.title === cards[0].title;
				const bHasSameTitle = b.title === cards[0].title;

				if (aHasSameTitle && !bHasSameTitle) return -1;
				if (!aHasSameTitle && bHasSameTitle) return 1;

				// If same title, sort by pack release date reversed
				if (a.title === b.title) {
					const aPackReleaseDate = moment(
						a.pack.date_release,
						"YYYY-MM-DD"
					);
					const bPackReleaseDate = moment(
						b.pack.date_release,
						"YYYY-MM-DD"
					);

					return aPackReleaseDate.isAfter(bPackReleaseDate) ? -1 : 1;
				}

				// Else sort by title
				return a.title.localeCompare(b.title);
			})
			.map(
				(card) =>
					`<li><a href="#" data-code="${card.code}" data-title="${card.title}">${card.title} (${card.pack.cycle.name})</a></li>`
			)
			.join("")}
      </ul>
    `;
	}
}
function clickOption() {
	const name = $(this).data("title");
	const code = $(this).data("code");
	const row = $(this).closest("li.list-group-item").find("a.card");
	row.data("code", code);
	row.find(".title").text(name);
	updateStats();
}

function updateStats() {
	let deckSize = 0;
	const types = {};
	$("#analyzed .card").each(function (_, element) {
		const card = $(element);
		const code = card.data("code");
		const qty = parseInt(card.data("qty"), 10);
		const record = NRDB.data.cards.findById(code);
		types[record.type.name] = types[record.type.name] ?? 0 + qty;
	});

	let html = "";
	for (const [type, amount] of Object.entries(types)) {
		if (type !== "Identity") {
			deckSize += amount;
			html += `
        <div>${amount} ${type}s</div>
      `;
		}
	}
	html = `<div><strong>${deckSize} Cards.</strong></div>${html}`;
	$("#stats").html(html);
	if ($("#analyzed li").length > 0) {
		$("#btn-save").prop("disabled", false);
	} else {
		$("#btn-save").prop("disabled", true);
	}
}
function do_save() {
	const deck = {};
	$("#analyzed .card").each(function (_, element) {
		const card = $(element);
		const code = card.data("code");
		const qty = parseInt(card.data("qty"), 10);
		deck[code] = qty;
	});
	$('input[name="content"]').val(JSON.stringify(deck));
}
