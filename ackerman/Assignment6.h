//***********************************************************************************
//Programmers: Kendall Belles
//Course     : CS-1513
//Program    : Assignment 6
//Date       : 11 December 2012
//Purpose    : Implement NumDays class as defined in the textbook on p. 768-9
//Input      : parameters only
//Calculate  : days from hours (8 hours = 1 day)
//Output     : return types only
//***********************************************************************************
#ifndef ASSIGNMENT6_H
#define ASSIGNMENT6_H

using namespace std;

class NumDays
{
	private:
		int hours;
	public:
		NumDays (int h=0)
		{
			hours = h;
		}
		~NumDays() { }
		int getHours ()
		{
			return hours;
		}
		double getDays ()
		{
			return hours / 8.0;
		}
		void setHours (int h)
		{
			hours = h;
		}
		void setDays (double days)
		{
			hours = int(days / 8);
		}
		NumDays operator+ (const NumDays &rhs)
		{
			NumDays temp((*this).hours + rhs.hours);
			return temp;
		}
		NumDays operator- (const NumDays &rhs)
		{
			NumDays temp((*this).hours - rhs.hours);
			return temp;
		}
		NumDays& operator++ ()
		{
			hours++;
			return *this;
		}
		NumDays operator++ (int)
		{
			NumDays temp = *this;
			hours++;
			return temp;
		}
		NumDays& operator-- ()
		{
			hours--;
			return *this;
		}
		NumDays operator-- (int)
		{
			NumDays temp = *this;
			hours--;
			return temp;
		}
};
#endif
