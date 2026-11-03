onload = function() {

      var myTable = new SortedTable();



      myTable.colorize = function() {

    for (var i=0;i<this.elements.length;i++) {

      if (i%2){

        this.changeClass(this.elements[i],'even','odd');

      } else {

        this.changeClass(this.elements[i],'odd','even');

      }

    }

  }

  myTable.onsort = myTable.colorize;

  myTable.onmove = myTable.colorize;

  myTable.colorize();

      myTable.onselect = function(element)

      {

        //get value on the table

          var $table = $("#sorted"),

            $headerCells = $table.find("thead th"),

            $rows = $table.find("tbody tr");

          var headers = [],

            rows = [];

          $headerCells.each(function(k,v)

          {

           headers[headers.length] = $(this).text();

          });

          $rows.each(function(row,v)

          {

          $(this).find("td").each(function(cell,v)

          {

            if (typeof rows[cell] === 'undefined') rows[cell] = [];

            rows[cell][row] = $(this).text();

          });

          });



          //get pkid value and submit the form

          document.employees['pkid'].value = rows[6][this.elementIndex(element)];

          document.employees.submit();

      }

  }