# Phase 3: Access databases through the creation of an interactive application

## Objectives

- Write SQL language to conduct database manipulation including insertion, deletion, search, and updating.
- Write an interactive application to manage a database by considering possible database operations.

## Tasks

Create an interactive application to manage the database that you created in Project: Phase 1. In all the later descriptions, the word interface is used to represent the way that users interact with the application. The interface does NOT need to be Graphical User Interface (GUI). You have flexibilities to design this interactive application. The goal is to make it work correctly and friendly to the user.

For the interactive application, you have two options:

- If you are familiar with any web development language (e.g., PHP, JavaScript, Python, or any other language), you can create a small web application as this interactive application.
- If you do not know any web development language, use either Java or Python to develop this interactive application. In this case, your application will be command line based.

## Functionalities

- **(10 points) Retrieve all data:** Given a table name, retrieve all data from the table and present it to the user.
- **(10 points) Average:** Given a table name and a column name, return the average of the column. Here the assumption is that the column type will be numeric (e.g., cost column).
- **(20 points) Insert:** Given a table name, your program should show the column names of that table and ask the users to input new data to the table. In case of errors, your program should directly show the MySQL errors to the users.
    - Assumption 1: Users will enter data according to the database constraints.
    - Assumption 2: Users will input one record at a time.
- **(20 points) Delete:** Given a table name, your program should show the column names of that table and ask the users to input data that they want to delete.
    - Assumption 1: Users will enter data according to the database constraints.
    - Assumption 2: Users will input one record at a time.
- **(10 points)** Create a README.txt file and explain how to run your code.
- **(10 points)** Your program should be correct in terms of compiling, taking parameters, connecting to the database, etc.
- **(20 points) Video Demonstration Submission**
    - Record a video showcasing the functionalities described above.
    - As students are using various database systems this semester, it may not be feasible for the TA to execute everyone's code on their own systems. Therefore, a video demonstration is required.
    - Use screen recording software (e.g., Zoom, OBS Studio, etc.) to create your video. Mobile phone recordings are not allowed and will result in a score of zero.
    - You do not need to include narration in your video, but you must demonstrate every functionality at least once.
    - Save your video as an MP4 file and include it in your ZIP file with the following naming format: "{YourLastName_YourFirstName} - Phase3 Video Recording.mp4".
    - Bonus Opportunity: Creative or fun video solutions may earn bonus points!

## Notes

- You can hardcode the database connection parameters (hostname, user id, database name, and password) in your program.

## Submission

- You have to submit your project electronically (through Canvas). Printed copies are not accepted.
- See course syllabus for policies on late submission and plagiarism.
- Please create a zip file to contain both the source files and the readme file, and name the zip file "p3\_(your last name).zip" (e.g., p3_Nagarkar.zip).

## Grading

- Points will be deducted if file naming conventions (mentioned above) are not followed.
- If your program cannot be compiled correctly, or cannot be run correctly, this phase of your project will be graded as ZERO even though your SQL can be run correctly through the interactive interface of any DBMS (e.g., MySQL Workbench).
- Your code will be tested on a test data and graded accordingly.

## Generative AI disclosure

For ChatGPT users:

- Submit a PDF file titled "GenAIQuestions.pdf" that includes the shared public link to your ChatGPT chat.

For users of other generative AI models:

- Submit a PDF file titled "GenAIQuestions.pdf" with:
    - A bulleted list of all questions you asked the generative AI.
    - The name of the generative AI model you used.
