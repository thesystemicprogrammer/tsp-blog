const INPUT_FILE = "public/api/index.json"
const OUTPUT_FILE_PUBLIC = "public/api/lunr_index.json"
const OUTPUT_FILE_STATIC = "static/api/lunr_index.json"

const fs = require("fs");
const lunr = require('lunr');

const indexFile = fs.readFileSync(INPUT_FILE);
const indexJson = JSON.parse(indexFile);

var idx = lunr(function () {
    this.ref('uri')
    this.field('title')
    this.field('description')
    this.field('topic')
    this.field('tags')
    this.field('content')

    this.metadataWhitelist = ['position']

    indexJson.forEach(function (doc) {
        this.add(doc)
    }, this)
})

//
// Write the file to public and static so we have it 
// for dev and for prod (ready for upload)
//
fs.writeFileSync(OUTPUT_FILE_PUBLIC, JSON.stringify(idx));
fs.writeFileSync(OUTPUT_FILE_STATIC, JSON.stringify(idx));

console.log("Lunr index created\n");
