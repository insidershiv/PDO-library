<?php
class MyDbHandler

{
    //$con: DataBase Connection
    //$tbname: Table Name
    private $con, $tbname;

    /* function::Constructor
       args  :: $con, $tbname
                  $con: DataBase Connection
                  $tbname: Table Name

       functionality:: Initilization
       Return:: NA
    */

        public  function MyDbHandler($con, $tbname) {
        $this->con = $con;
        $this->tbname = $tbname;

    }


     /* function::getAll
         @args - any number of args
         #args rule#
            if(args) -----> args = array of column name
                             eg: args = array("col_name1", "col_name2");
                             query returns row(s) with specific columns in the array mentioned;

            if: args = 0 -------> query returns all row(s) with all columns


       functionality:: getting all the rows from the table
       Return:: (if Successfull) -----> returns rows from the table
                (if Failed)     ------> returns false
    */

    public function getAll(){
        $args = func_num_args();
        if($args){
            $arg_list = func_get_args();
            $query = "SELECT " . $this->getOrderString($arg_list[0])." FROM " . $this->tbname;
        }
        else {
           $query = "SELECT * FROM $this->tbname";
        }

        $stmt = $this->con->prepare($query);
        $result = $stmt->execute();
        if($result){
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }else {
            return false;
        }
    }


    /* function:: get
       @args ------>Maximum 2
       #args rule#

            if(args==1) ---------> args = array of "WHERE" clause
                                   eg: args = array("id"=>31,"name" => "abc","class"=>2);

                                   query returns all rows and all colums satisfying the WHERE Clause


            if(args==2) ---------> first arg = array of column which are to be shown
                                   eg: arg1 = array("name', "email");

                                   second args = array of WHERE Clause
                                   eg:    arg2 = array("id"=>31,"name" => "abc","class"=>2);

                                   Query returns all rows and specified columuns in arg1 array satisfying the Where Clause given in arg2


       functionality :query executes and  returns rows as per "Select Statement" and "WHERE" Clause with the args

       Return:: if(successfull)--------> returns with the resultant rows;
                if(failed)----------> returns false;
    */



    public function get(){
        $args = func_num_args();
        $args_list = func_get_args();
        $result = false;
        if($args == 1){
          $query  = "SELECT * FROM $this->tbname WHERE " . $this->getWhereString($args_list[0], 'AND');
          $stmt = $this->con->prepare($query);
          $result = $stmt->execute($args_list[0]);
        }
        if($args == 2){
           $query = "SELECT " . $this->getOrderString($args_list[0]) . " FROM  $this->tbname WHERE " . $this->getWhereString($args_list[1], 'AND');
            $stmt = $this->con->prepare($query);
            $result = $stmt->execute($args_list[1]);
        }
        if($result){
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }else {
            return false;
        }
    }


    /* function:: insert
       @args ------>1
       #args rule#

       args($data)------------------->$data = is the array of the values you want to insert;
                                      eg:$data = array("name"=>'abcd', "email"=>'dd@k.com');


        functionality: Inserts Into the Table

        Return: if(successfull)-------> return true
                if(failed)------------> return false
    */


    public function insert($data){
        $str = "";
        $length = count($data);
        $loop =0;
        foreach($data as $key => $value){
            $str .=  $key;
            if($loop !=$length-1){
                $str .= ' , ';
            }
            $loop++;
        }
       $query = "INSERT INTO ". $this->tbname."(".$str.")"." values(".$this->getBindString($data).")";
       $stmt = $this->con->prepare($query);
       return $stmt->execute($data);
    }


    /*  function :: getOrderString(Private)
        @args------>1;
        #args rule#

        arg($order)---------> $order is an array
                              eg:$order = array("name", "email", "comment");


        functionality: convert array elements into string and appends comma(,) after each array element except after the last element
                       It is used to make queries where comma(,) is required

        Return::    returns converted String from the array given in the arg

    */

    private function getOrderString($order) {
        $str = "";
        $length = count($order);
        $loop =0;

        while($loop != $length-1) {

            $str .= $order[$loop] . ',';
            $loop++;
        }
        $str .= $order[$length-1];
        return $str;
    }


    /*  function :: getBindString(Private)
        @args------->1
        #args rule#

        arg($data)---------> $data is an array
                              eg:$data = array("name"=>'abcd', "email"=>'dd@k.com');


        functionality:  convert array elements into string and appends colon(:) before each 'key' of array and
                        appends comma(,) after each 'key' except after the last element
                        It is used to make queries where colon(:) and comma(,) is required

        Return::    returns converted String from the array given in the arg
    */

    private function getBindString($data) {
        $str = "";
        $length = count($data);
        $loop =0;
        foreach($data as $key => $value){


            $str .= ':' . $key;
            if($loop !=$length-1){
                $str .= ',';
            }
            $loop++;
        }
        return $str;
    }


    /*  function :: delete
        @args------->1
        #args rule#

        arg($condition)---------> $condition is an array
                              eg: $condition = $condition = array("id"=>33);


        functionality:  Deletes the rows in which "WHERE Clause" is satisfied the conditons for WHERE Clause is
                        Given in arg($condition);

        Return::    if(successfull)--------> returns true;
                    if(failed)-------------> returns false;



    */

    public function delete($condition){
        $query = "DELETE FROM $this->tbname WHERE " . $this->getWhereString($condition, 'AND');
        $stmt = $this->con->prepare($query);
        return $stmt->execute($condition);
    }



    /*  function :: update
        @args------->2(required)
        #args rule#

        arg($data)---------> $data is an array holds the table name as key and values are the values which are to be
                             put in the table.
                             eg: $data = array("name"=>'abcd', "email"=>'dd@k.com');


        arg($condition)----->$condition is an array depicting the WHERE Clause for the Updatation.
                             eg: $condition = array("id"=>33);

        functionality::      Updates the table with the values given in the arg($data) and as per the WHERE Clause
                             given in the arg($condition).

        Return::    if(successfull)--------> returns true;
                    if(failed)-------------> returns false;
    */

    public function update($data,$condition){
     $query = "UPDATE $this->tbname SET " .  $this->getWhereString($data,',') .     " WHERE " . $this->getWhereString($condition,'AND');
     $stmt = $this->con->prepare($query);
     return $stmt->execute(array_merge($data,$condition));
    }


    /* function :: getWheredString(Private)
        @args------->2(required)
        #args rule#

        arg($data)---------> $data is an array
                              eg:$data = array("name"=>'abcd', "email"=>'dd@k.com');


        arg($val)----------> $val is the character you want put at the end of each key of array key
                             eg:$val:('AND');

        functionality:  convert array elements into string and appends =: after each 'key' of array and
                        appends $val at the end of last key after each 'key' except after the last element
                        It is used to make queries with "WHERE" Clause which has colon(=:) and (AND);

        Return::    returns converted String from the array given in the args

    */

    private function getWhereString($data,$val) {
        $str = "";
        $length = count($data);
        $loop =0;
        foreach($data as $key => $value){
            $str .= $key.'= '.':' . $key;
            if($loop !=$length-1){
                $str .= " $val ";
            }
            $loop++;
        }
        return $str;

    }


    /*  function :: checkExistence
        @args------->1
        #args rule#

        arg($condition)---------> $condition is an array
                              eg: $condition = $condition = array("id"=>33);


        functionality:  checks if there is any row satisying the "WHERE Clause" given as $conditons


        Return::    if(successfull)--------> returns true;
                    if(failed)-------------> returns false;
    */

    public function checkExistence($condition){
        $query = "SELECT * FROM $this->tbname WHERE " . $this->getWhereString($condition, 'AND');
        $stmt = $this->con->prepare($query);
        $result = $stmt->execute($condition);
        if($result){
            $data = $stmt->fetchAll();
            if(count($data) != 0){
                return true;
            }
            else{
                return false;
            }
        }else {
            return false;
        }
    }

}
?>
