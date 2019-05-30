This code tracks a DSpace Repository and mints DOIs for items if requested.

Now it works with DSpace 5 REST API and the DataCite MDS API. The generated DataCite XML is compliant with DataCite Schema version 4.1.

The code relies on a local MySQL DB, the structure is on "doiMinter.sql"

To run the code there are three tasks located in tasks folder:
  1. harvest.php
  2. registerDOIs.php
  3. updateMetadata.php
  
A poster describing this work was presented at OR2019 and is available at: https://repository.kaust.edu.sa/handle/10754/653099
