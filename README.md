# SearchDocs
Search engine for your your documents.
TO run the application.

Install Composer.

Then do the composer update, so it will download all the packages needed for the projects.

Command to run has following format - 

php search_program.php some_dir query ranking_method tokenization_method

where,

search_program.php - Entry Page.

some_dir/ - is the path for directory containing .txt files, where you want to search.

"apple records" - Is the query you want to give to search engine.

ranking_method - ranking method to use to rank the documents acording to query.
                 currently supported ranking methods are cosine, proximity, bm25

tokenization_method - It specifies how you want to tokenise the data in files,
                      current tokenizer supported are "none" "stem" "chargram".





