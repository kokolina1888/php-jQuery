console.log('yes');
function validDate(date)
{
	var pattern = "/^(\d{4}(-\d{2}){2} (\d{2}:){2}(\d{2}))$/";
	//returns tre if if the date matches, false if it doesn`t
	return date.match(pattern)!= null;
}