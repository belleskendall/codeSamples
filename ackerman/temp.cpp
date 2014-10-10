#include <iostream>
#include "Assignment6.h"
using namespace std;

int main()
{
	NumDays test1;
	NumDays test2(4);
	NumDays test3;
	cout << test1.getHours() << " " << test2.getHours() << endl;
	cout << test1.getDays() << " " << test2.getDays() << endl;
	test1.setHours(8);
	test2.setHours(24);
	cout << test1.getHours() << " " << test2.getHours() << endl;
	cout << test1.getDays() << " " << test2.getDays() << endl;
	test3 = test1 + test2;
	cout << test3.getHours() << " " << test3.getDays() << endl;
	test3 = test3 - test1;
	cout << test3.getHours() << " " << test3.getDays() << endl;
	test1++;
	test2--;
	cout << test1.getHours() << " " << test2.getHours() << endl;
	++test1;
	--test2;
	cout << test1.getHours() << " " << test2.getHours() << endl;
}