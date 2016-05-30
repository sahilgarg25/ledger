<?php 

function mysqlQuery($sql) {
	$servername = "localhost:3306";
	$username = "root";
	$password = "root";
	$dbname = "ledger";

	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
	// Check connection
	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	} 

	//echo $sql;
	$result = $conn->query($sql);

	if(!$result) {
	    echo "Error: " . $sql . "<br>" . $conn->error;
	}

	$conn->close();

	return $result;
}

function getAccounts($type = null) {
	$sql = "SELECT * FROM accounts";
	if ($type) {
		$sql .= " WHERE type = '".$type."'";
	}
	$sql .= " ORDER BY name";
	$accountRows = mysqlQuery($sql);
	$accounts = array();
	while($account = $accountRows->fetch_assoc()) {
		array_push($accounts, $account);
	}
	return $accounts;
}

function getAccountById($id) {
	$sql = "SELECT * FROM accounts WHERE id = ".$id;
	$accountRows = mysqlQuery($sql);
	$accounts = array();
	while($account = $accountRows->fetch_assoc()) {
		array_push($accounts, $account);
	}
	return $accounts[0];
}

function addAccount($name, $type) {
	$sql = "INSERT INTO `accounts` (`name`, `type`) VALUES ('".$name."', '".$type."')";
	return mysqlQuery($sql);
}

function getTransactions($id = null) {
	$sql = "SELECT t.date AS date, t.description AS description, t.amount AS amount, fa.id AS from_account_id, fa.name AS from_account_name, ta.id AS to_account_id, ta.name AS to_account_name FROM transactions t JOIN accounts fa ON t.from_account = fa.id JOIN accounts ta ON t.to_account = ta.id";
	if ($id) {
		$sql .= " WHERE t.from_account = ".$id." OR t.to_account = ".$id;
	}
	$sql .= " ORDER BY date desc";
	$txnRows = mysqlQuery($sql);
	$txns = array();
	while($txn = $txnRows->fetch_assoc()) {
		array_push($txns, $txn);
	}
	return $txns;
}

function addTransaction($fromAccount, $toAccount, $description, $amount, $date) {
	$sql = "INSERT INTO `transactions` (`from_account`, `to_account`, `description`, `amount`, `date`) VALUES (".$fromAccount.", ".$toAccount.", '".$description."', ".$amount.", '".$date."')";
	return mysqlQuery($sql);
}

function getBalanceByType($type) {
	$sql = "SELECT sum(amount) AS from_amount FROM transactions t JOIN accounts fa ON t.from_account = fa.id WHERE fa.type = '".$type."'";
	$txnRows = mysqlQuery($sql);
	$fromAmount = 0;
	while($txn = $txnRows->fetch_assoc()) {
		$fromAmount += $txn['from_amount'];
	}
	$sql = "SELECT sum(amount) AS to_amount FROM transactions t JOIN accounts ta ON t.to_account = ta.id WHERE ta.type = '".$type."'";
	$txnRows = mysqlQuery($sql);
	$toAmount = 0;
	while($txn = $txnRows->fetch_assoc()) {
		$toAmount += $txn['to_amount'];
	}
	return $toAmount - $fromAmount;
}

function getBalanceByAccountId($id) {
	$sql = "SELECT sum(amount) AS from_amount FROM transactions t JOIN accounts fa ON t.from_account = fa.id WHERE fa.id = '".$id."'";
	$txnRows = mysqlQuery($sql);
	$fromAmount = 0;
	while($txn = $txnRows->fetch_assoc()) {
		$fromAmount += $txn['from_amount'];
	}
	$sql = "SELECT sum(amount) AS to_amount FROM transactions t JOIN accounts ta ON t.to_account = ta.id WHERE ta.id = '".$id."'";
	$txnRows = mysqlQuery($sql);
	$toAmount = 0;
	while($txn = $txnRows->fetch_assoc()) {
		$toAmount += $txn['to_amount'];
	}
	return $toAmount - $fromAmount;
}

?>