#include <stdlib.h>
#include <stdio.h>
#include <string.h>
#include <pthread.h>

//error count for Sudoku
int errors = 0;
//Sudoku grid
char grid[9][9];

//helper function to validate input
void getGrid();
//checks an array for 1-9 exactly once each
void *checkGroup(void* line);

int main() {
	printf("Enter the Sudoku to be validated with no spaces, 9 numbers to a line, no blank lines.\n");
	getGrid();

	//if an error was detected in entering the Sudoku, abort the program
	if (errors != 0) {
		printf("An error was detected while entering the Sudoku. Please try again.\n");
		return 0;
	}

	//pthreads to check colums, rows, and sub-graphs
	pthread_t colChecker, rowChecker, subChecker[3];
	pthread_attr_t attr;
	pthread_attr_init(&attr);

	//temp arrays for passing info to checker function, NULL terminated
	char tempA[10], tempB[10], tempC[10];
	tempA[9] = tempB[9] = tempC[9] = '\0';

	int i;
	int j;
	//for each row/column of the Sudoku...
	for(i=0; i<9; i++) {
		//for each element in the row/column, copy element to temp array until full
		for(j=0; j<9; j++) {
			tempA[j] = grid[i][j];
			tempB[j] = grid[j][i];
		}

		//use children pthreads and checker function to detect correct rows/columns
		pthread_create(&colChecker, &attr, checkGroup, tempA);
		pthread_create(&rowChecker, &attr, checkGroup, tempB);
		pthread_join(colChecker, NULL);
		pthread_join(rowChecker, NULL);
	}

	//build temp arrays from sub-grids 3 at a time
	int delim = 0;
	//for each row of the Sudoku...
	for(i=0; i<9; i++) {
		//copy elemnts from grid to temp array in groups of 3
		for(j=0; j<3; j++) {
			tempA[3*delim+j]=grid[i][j];
			tempB[3*delim+j]=grid[i][j+3];
			tempC[3*delim+j]=grid[i][j+6];
		}
		delim = (delim + 1)%3;
		//every 3rd row of the Sudoku, 3 sub-grids are ready to be checked
		//by child threads and the checker function
		if ((i+1)%3 == 0) {
			pthread_create(&subChecker[0], &attr, checkGroup, tempA);
			pthread_create(&subChecker[1], &attr, checkGroup, tempB);
			pthread_create(&subChecker[2], &attr, checkGroup, tempC);
			pthread_join(subChecker[0], NULL);
			pthread_join(subChecker[1], NULL);
			pthread_join(subChecker[2], NULL);
		}
	}

	//if no erros found (input+checking), notify user of valid solution
	if (errors == 0)
		printf("Congratulations, this is a valid Sudoku solution.\n");
	else
		printf("I'm sorry. This is not a valid Sudoku solution.\n");

	return 0;
}
void getGrid() {
	//row and column
	int r=0, c=0;
	char in;
	//while no errors detected, check each character and row length for validity (1-9 only)
	//until grid[][] is full
	do {
		in = getchar();
		//if 9 numbers entered, but line is not returned with next keystroke, the line
		//is too long
		if (c == 9 && in != '\n') {
			errors++;
			printf("You have attempted to enter too many characters on the previous line.\n");
			break;
		}
		switch(in) {
			case '1': {
				grid[r][c] = in;
				c++;
				break;
			}
			case '2': {
				grid[r][c] = in;
				c++;
				break;
			}
			case '3': {
				grid[r][c] = in;
				c++;
				break;
			}
			case '4': {
				grid[r][c] = in;
				c++;
				break;
			}
			case '5': {
				grid[r][c] = in;
				c++;
				break;
			}
			case '6': {
				grid[r][c] = in;
				c++;
				break;
			}
			case '7': {
				grid[r][c] = in;
				c++;
				break;
			}
			case '8': {
				grid[r][c] = in;
				c++;
				break;
			}
			case '9': {
				grid[r][c] = in;
				c++;
				break;
			}
			//if line break, check current column count, 9 is valid
			//all else means the line was too short
			case '\n': {
				if (c == 9) {
					r++;
					c=0;
					break;
				}
				else {
					errors++;
					printf("The previous line is too short.\n");
					break;
				}
			}
			//not 1-9 or '\n'
			default: {
				errors++;
				printf("An invalid character was found.\n");
				break;
			}
		}
	} while (r<9 && errors == 0);
}

void *checkGroup(void* line) {
	char *checkMe = (char*)line;
	int checked[] = {0,0,0,0,0,0,0,0,0};

	int i=0;
	//while not the NULL terminator of arguement, convert given
	//char to index and increment value at that index
	while (checkMe[i] != '\0') {
		checked[((int)checkMe[i])-49]++;
		i++;
	}

	//check to make sure that each number was seen exactly once in 
	//passed array
	i=0;
	while (i<9) {
		if (checked[i] != 1)
			errors++;
		i++;
	}
}
