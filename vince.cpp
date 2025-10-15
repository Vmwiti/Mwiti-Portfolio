#include <iostream>
#include <vector>
#include <string>

using namespace std;

struct Student {
    int rollNumber;
    string name;
    int age;
};

vector<Student> students;

void addStudent() {
    Student s;
    cout << "Enter roll number: ";
    cin >> s.rollNumber;
    cin.ignore(); // clear input buffer
    cout << "Enter name: ";
    getline(cin, s.name);
    cout << "Enter age: ";
    cin >> s.age;

    students.push_back(s);
    cout << "Student added successfully!\n";
}

void displayStudents() {
    cout << "\n--- Student List ---\n";
    for (const auto& s : students) {
        cout << "Roll No: " << s.rollNumber
             << ", Name: " << s.name
             << ", Age: " << s.age << "\n";
    }
    if (students.empty()) {
        cout << "No students found.\n";
    }
}

void searchStudent() {
    int roll;
    cout << "Enter roll number to search: ";
    cin >> roll;

    for (const auto& s : students) {
        if (s.rollNumber == roll) {
            cout << "Student found: " << s.name << ", Age: " << s.age << "\n";
            return;
        }
    }
    cout << "Student with roll number " << roll << " not found.\n";
}

int main() {
    int choice;
    do {
        cout << "\n--- Student Management System ---\n";
        cout << "1. Add Student\n";
        cout << "2. Display All Students\n";
        cout << "3. Search Student\n";
        cout << "4. Exit\n";
        cout << "Enter your choice: ";
        cin >> choice;

        switch (choice) {
            case 1: addStudent(); break;
            case 2: displayStudents(); break;
            case 3: searchStudent(); break;
            case 4: cout << "Exiting...\n"; break;
            default: cout << "Invalid choice. Try again.\n";
        }
    } while (choice != 4);

    return 0;
}
