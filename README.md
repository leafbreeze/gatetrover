# gatetrover

Project FIERCE FLAGG

Objective
---------------------------------------------------------
Create an application for collecting G.I.Joe comicbooks.

At-A-Glance
---------------------------------------------------------
This project is an experiment to determine if available website information can be captured, transformed it into logical data elements, and then rebuilt using available frameworks. The experiment uses PHP and DOMXpath (PHP Simple HTML DOM Parser -- http://sourceforge.net/projects/simplehtmldom/) to reverse engineer a CGI application, MySQL to capture the results, and HTML/CSS/JavaScript to develop a responsive user interface.

Lessons Learned
---------------------------------------------------------
DOMXpath expression is simple to use, while the DOM Traversal code is four times faster. Running the code 10,000 times translates into half a second to execute the XPath code and approximately 0.13 seconds to execute the DOM code. The choice is either to sacrific a few microseconds of processor time in exchange for simper code, or have a much faster execute time regardless of the complexity.
