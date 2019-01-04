---
title: "Learn HTML"
subtitle: "HTML is to websites what columns are for buildings. 

Learn the basics of HTML - the foundation of the web."
time: "12 minutes"
date: "2018-31-10"
tags: ["fale"]
---

[[info]]
| :point_up: Since in the previous chapter we equated houses, stores and buildings to web pages, now we have to say that HTML are the blueprints.


# **HTML is the Website Skeleton**
***

All web pages have HTML – it’s the structure of EVERYTHING.  Think of it as building columns at a construction site.

HTML makes you divide the website information into parts – similar to the basic parts of a document: header, title, content, footnote, subtitle, etc.  Then, with CSS, you can make your page beautiful, and, with JavaScript, make it interactive.

Originally browsers only knew how to interpret HTML.  Websites were simple and neither CSS or JavaScript was used.  A website was a simple plain text document with the typical elements any Word Document has: Headings, Bullet lists, Paragraphs, etc.

![buildinghtml](https://ucarecdn.com/aa1a5994-8de9-4d24-99ce-3a0d686c30bd/-/resize/700x/)


All tags must open and close.  To close a tag you must place the same word but using the `/` symbol.

# **The Attributes**
***
Once the `<tag>` is defined, we can describe in detail its behavior by assigning attributes to those `<tags>`.  For example, if we want our HTML document/page to have a link to another page, we use the `<a>` tag, and we assign to it an attribute called **href**, which allows us to specify the URL of the page with which we want to have a connection.

```html
<a href="google.com">Click here and it will take you to Google.com</a>
```


In theory, you have to use [one of these tags](https://projects.breatheco.de/d/landing-page-with-react#readme) and don’t invent your own because the browser won’t know how to interpret them.  You must learn what each tag means and does in order to put them to good use…but, please, don’t worry!   There aren’t that many! 🙂

For the main heading of the document, the tag that we use is `<h1>`.  For example: An online store has an "electronics" category, the title that applies would be "Electronics" and the `<h1>` tag would be written as follows:

```html
 <h1>Electronic items</h1>
```


##### **Nested Tags** :
Finally, tags can contain one or more tags within them.  For example, if we would like to give a cursive style to the word "electronic" we must wrap that word with the tag `<i>`:

```html
 <h1><i>Electronic</i> Tags</h1>
```

 ## Blank Spaces and Line Jumps 
 ***
 The browser ignores blank spaces and end of lines.  If we want to jump one line, we have to use the `<br>` tag.  If we want more "spaces" we need to insert one `&nbsp;` per each blank space (yes, we know it’s weird, but it is what it is).

**These three alternatives will look the same (spaces and jumps of line will be ignored):**
```html
<tag>Hello</tag><tag>World</tag>
```
```html
<tag>Hello</tag>
<tag>World</tag>
```

```html
<tag>Hello</tag>               <tag>World</tag>
```


# **Page Structure**
***
All pages must begin with the `<DOCTYPE! Html>` statement, then the `<HEAD>` and the `<BODY>` should follow.  These tags **must** contain other tags within them (nested tags) because they will split the page in 2 main parts: the HEAD and the BODY:


```html
<! –- We must always begin with an HTML label to show the browser that this is a document with an HTML format. — >
<!DOCTYPE html>
<html>
   <head>
   <! — Inside the head tag we will define all that the browser needs BEFORE it begins to interpret the page. –>
   </head>
   <body>
   <!–Inside the body we will define the real content of the page.–>
   </body>
</html>
```

Lets simulate how a browser thinks: Imagine a user on his browser (client side) that types the URL: breatheco.de

+ The server will open the default HTML file on that server, which will probably be: index.html.
+ Then, it will read its content and interpret it as HTML (because the extension of the file is index.html).
+ The user will not see the text content of the file, instead it will view a visual interpretation of that text.
  
  As you can see, the page in question will include AT LEAST the following tags:

  ![html](https://ucarecdn.com/8729c2f0-e4a6-4721-9ee9-3f29e6e852b5/)

|**Name**   |**Tags**   |**Description**   |
|:----------|:----------|:-----------------|
|HTML       |`<html>`   |We must begin by letting the browser know that this is an HTML document.  We can also specify the HTML version that we are using.   |
|Head       |`<head>`   |Everything that is written inside of the HEAD won’t be seen by the user.  It’s the part of the page where the developer specifies information about the website itself: the language being used, what the website is about, the necessary fonts, the icon that the tab will have on the browser (favicon), and many other important things.   |
|Body       |`<body>`   |Here you will place all the content that will be viewed by the end user.<br>If this were MS Word, the body would mark the beginning of your page content (the first line of your document).   | 

# **The \<HEAD\> is like the Envelope of a Letter.**
***
We read the envelope of a letter to find out information of the letter itself, but not of its content.  Here you can find out who wrote the letter, in what language is it written, where is it from, etc.

In the case of HTML, the `<head>` can contain the following tags (among less important ones):

|**Name**   |**Tag**   |**Description**   |
|:----------|:---------|:-----------------|
|Title   |`<title>`   |The title appears in the browser’s window, it’s also used when you share the page through social media: Twitter, Instagram, Facebook, etc.  All those networks use the title of the page as the excerpt when a user copies the URL of your page to share on their wall.   |
|Meta   |`<meta>`   |The meta tags describe a document.  They are used to specify things like: the author, title, date, keywords, descriptions, etc.   Search engines love these tags because they allow an easier comprehension of the content before it is read.   |
|Link   |`<link>`   |Used for linking the page with the CSS style sheets.  In the CSS chapter we will learn how to create style sheets and we will be able to import them using this tag.   |
|Style   |`<style>`   |If we can’t or don’t want to import a CSS style sheet, we may also define styles directly on the HTML document inside this tag.  This is a practice that we rarely recommend and should only be used when you don’t have any other choice.   |
|Script   |`<script>`   |Used to add JavaScript code to the page.  All of the JavaScript code must be contained in these tags that can be used also in the BODY, if desired.  The difference is that any JavaScript code that we place in a style tag in the BODY won’t be available when the page begins to run (that’s exactly why the is HEAD is so useful).   |

# **The \<body\> is Similar to any MS Word Document**

Ok, now that we are familiar with the general and necessary structure of the page, lets review the tags we can and must use to define the content of the page.

Remember – for the fifteenth time – that a web page is…a text document!  That’s right, if you knew the answer before you read it you are getting it!  And, if not, don’t worry.  We’ve never known of anyone who gets HTML and CSS rather quickly ;).

Lets see how a website compares to a Word document:
