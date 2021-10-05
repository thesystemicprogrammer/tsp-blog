const MAX_RESULTS_PER_PAGE = 2;
const MAX_SUMMARY_LENGTH = 100
const SENTENCE_BOUNDARY_REGEX = /\b\.\s/gm
const WORD_REGEX = /\b(\w*)[\W|\s|\b]?/gm

var idx = null;
var offset = 0;
var searchTerms = ""

$(document).ready(function () {

    $("#load-more").hide();

    $.getJSON("/api/lunr_index.json", function (data) {
        idx = lunr.Index.load(data);
    });

    $("#search").on("keypress", function (e) {
        if (e.keyCode == 13) {
            $("#results").html("");
            searchTerms = $("#search").val()
            search()
            return false;
        }
    });

    $("#load-more").click(function () {
        $("#load-more").html("Loading ...");
        offset += MAX_RESULTS_PER_PAGE;
        search();
    });
});

function search() {
    const results = idx.search(getLunrSearchQuery(searchTerms));
    if (results.length > 0) {
        var toResult = offset + MAX_RESULTS_PER_PAGE;

        if (toResult < results.length) {
            $("#load-more").show()
        } else {
            $("#load-more").hide();
            toResult = results.length;
        }

        for (var i = offset; i < toResult; i++) {
            $.getJSON(`/api/${results[i]["ref"]}/index.json`, function (article) {
                const searchQueryRegex = new RegExp(createQueryStringRegex(searchTerms), "gmi");
                resultBlurp = createSearchResultBlurb(searchQueryRegex, searchTerms, article.content)
                resultText = `
                        <span class="is-size-7">${ article.uri}</span>
                        <a href="${article.uri}">
                            <h1 class="is-size-5 mb-1">${ markText(searchQueryRegex, article.title) }</h1>
                        </a>
                        <p>${resultBlurp}</p>`;
                $("#results").append(resultText);
            });
        }
    } else {
        $("#results").html("<p>No results found...</p>");
    }
}

function markText(searchQueryRegex, testString) {
    return testString.replace(
        searchQueryRegex,
        "<mark>$&</mark>"
    );

}

function getLunrSearchQuery(query) {
    const terms = query.split(" ");
    if (terms.length === 1) {
        return query;
    }
    query = "";
    for (const term of terms) {
        query += `+${term} `;
    }
    return query.trim();
}

function createSearchResultBlurb(searchQueryRegex, query, pageContent) {
    const searchQueryHits = Array.from(
        pageContent.matchAll(searchQueryRegex),
        (m) => m.index
    );
    const sentenceBoundaries = Array.from(
        pageContent.matchAll(SENTENCE_BOUNDARY_REGEX),
        (m) => m.index
    );
    let searchResultText = "";
    let lastEndOfSentence = 0;
    for (const hitLocation of searchQueryHits) {
        if (hitLocation > lastEndOfSentence) {
            for (let i = 0; i < sentenceBoundaries.length; i++) {
                if (sentenceBoundaries[i] > hitLocation) {
                    const startOfSentence = i > 0 ? sentenceBoundaries[i - 1] + 1 : 0;
                    const endOfSentence = sentenceBoundaries[i];
                    lastEndOfSentence = endOfSentence;
                    parsedSentence = pageContent.slice(startOfSentence, endOfSentence).trim();
                    searchResultText += `${parsedSentence} ... `;
                    break;
                }
            }
        }
        const searchResultWords = tokenize(searchResultText);
        const pageBreakers = searchResultWords.filter((word) => word.length > 50);
        if (pageBreakers.length > 0) {
            searchResultText = fixPageBreakers(searchResultText, pageBreakers);
        }
        if (searchResultWords.length >= MAX_SUMMARY_LENGTH) break;
    }
    return ellipsize(searchResultText, MAX_SUMMARY_LENGTH).replace(
        searchQueryRegex,
        "<mark>$&</mark>"
    );
}

function createQueryStringRegex(query) {
    const searchTerms = query.split(" ");
    if (searchTerms.length == 1) {
        return query;
    }
    query = "";
    for (const term of searchTerms) {
        query += `${term}|`;
    }
    query = query.slice(0, -1);
    return `(${query})`;
}

function tokenize(input) {
    const wordMatches = Array.from(input.matchAll(WORD_REGEX), (m) => m);
    return wordMatches.map((m) => ({
        word: m[0],
        start: m.index,
        end: m.index + m[0].length,
        length: m[0].length,
    }));
}

function ellipsize(input, maxLength) {
    const words = tokenize(input);
    if (words.length <= maxLength) {
        return input;
    }
    return input.slice(0, words[maxLength].end) + "...";
}
