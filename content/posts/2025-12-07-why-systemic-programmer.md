---
title: 'Why this Blog is called The Systemic Programmer'
date: 2025-12-07T11:10:03+01:00
# created: YYYY-MM-DDTHH:MM:SS+TZ (uncomment and set to show both created and updated dates)
slug: 'why-systemic-programmer' 
draft: false
pinned: true 
categories: 
- systemic
tags: 
- foundations
---

As software engineers, we love predictability. We love to analyse problems, break them down into manageable pieces, and build elegant solutions. We're trained to believe that with enough analysis, enough planning, enough upfront thinking, we can understand any system and predict its behaviour. For years, I chased that ideal—the perfect analysis that would prevent all failures.
<!--more-->

But here's the thing: no matter how thorough my analysis, some projects still went sideways. Not because of technical failures, but because of shifting stakeholder priorities, organizational politics, or requirements that seemed clear until they weren't. I kept thinking, "I just need to analyse better, deeper, more completely."

Then I discovered systems thinking, and everything changed.

This discovery didn't just change how I work—it fundamentally transformed how I understand software engineering itself. It's why I call myself a "systemic programmer," and why this blog exists. If you've ever struggled to make sense of agile, or wondered why it feels impossible to analyse complex projects properly upfront, then this journey might resonate with you too.

## The Illusion of Control

At the beginning of my career—and we're talking about 30 years ago, long before the [Agile Manifesto](https://agilemanifesto.org) was even written—I operated under a simple belief: If something failed, I just hadn't analysed it thoroughly enough. Give me more time, better tools, more comprehensive requirements gathering, and I'd get it right.

The dominant methodology back then was the [Rational Unified Process](https://en.wikipedia.org/wiki/Rational_unified_process) (RUP), with its beautifully structured phases and iterations. It felt like the answer to all my prayers. Here was a framework that valued deep analysis, careful planning, and systematic thinking. I dove deep into requirements engineering, convinced that mastering the art of capturing and analysing requirements would be my key to building flawless systems.

And you know what? For purely technical problems, this approach worked brilliantly. When I was designing database schemas, optimizing algorithms, or architecting API layers, thorough analysis paid off. The more I understood the problem space, the better my solutions became.

But then there were those other projects. The ones where I'd done everything "right"—comprehensive requirements documents, detailed analysis models, stakeholder sign-offs — and yet they still failed. Not because of bugs or technical debt, but because the business priorities shifted mid-project. Or because two departments had fundamentally different visions that no one had articulated. Or because what users said they wanted wasn't actually what they needed.

I'd blame it on insufficient analysis. "Next time," I'd tell myself, "I'll dig even deeper." But deep down, something nagged at me. It felt like I was missing a fundamental piece of the puzzle, but I couldn't articulate what it was.

## The Awakening

The missing piece revealed itself years later, in the most unexpected way.

I had decided to pursue a [Master's degree in Software Engineering at the Open University](https://www.open.ac.uk/postgraduate/qualifications/f66) in Milton Keynes. The OU was perfect for my situation—I could balance a demanding career and family life while still pursuing advanced education. The program itself was excellent, with great tutors and a curriculum that covered everything you'd expect: advanced software architecture, formal methods, project management.

But it was two modules that weren't strictly about software engineering that changed everything: [Systems Thinking in Practice](https://www.open.ac.uk/postgraduate/modules/tb871/) and its follow-up module, [Systems Thinking](https://www.open.ac.uk/postgraduate/modules/tb872/). I almost didn't take them—they seemed tangential to my goal of becoming a better software engineer.

Thank goodness I did.

Here's the thing: as a software engineer, you're already intimately familiar with systems. You think in systems every day. You understand how components interact, how data flows through architectures, how changes in one part ripple through others. But what I discovered was that I'd been thinking about systems in a very narrow, technical way.

These modules opened my eyes to a completely different dimension. They taught me to think about software projects not just as technical systems, but as systems embedded within organizational systems, human systems, political systems. The technical architecture was just one layer—and often not even the most important one for project success.

The real "aha!" moment came when I learned about the distinction between complicated and complex systems. Suddenly, all those project failures that I'd blamed on "insufficient analysis" made perfect sense. I hadn't been analysing wrong—I'd been using the wrong mental models entirely.

It wasn't about analysing harder. It was about thinking differently. 

## Complicated vs. Complex: The Distinction That Changes Everything

Let me explain why this distinction matters so much for software engineers.

**Complicated systems** are systems you can analyse, decompose, and understand through careful study. They might have many parts and intricate interactions, but they're ultimately predictable. Given enough expertise and time, you can figure them out. Most of the purely technical problems we face fall into this category:

- Optimizing a database query for performance
- Designing a scalable micro services architecture  
- Implementing a distributed caching strategy
- Debugging a race condition in concurrent code

These are hard problems—don't get me wrong. They require deep expertise and careful thinking. But they're fundamentally analysable. The better your analysis, the better your solution. Your engineering training serves you beautifully here.

**Complex systems**, on the other hand, are different beasts entirely. They involve multiple agents (often people) with their own goals, perspectives, and agency. They're adaptive—they change in response to your interventions. Cause and effect are only clear in retrospect, if at all. You cannot fully analyze them upfront because they don't have a fixed state to analyse.

Here's where software engineering gets interesting: consider these scenarios that most of us have faced:

- A product owner says they want feature X, but what they really need is something else they haven't articulated
- Two departments both claim they're the primary stakeholder, each with conflicting requirements
- The executive who championed your project leaves, and suddenly priorities shift
- Users resist adopting the new system, even though it's technically superior to the old one
- Legal requirements change mid-project based on a new regulation
- Your perfectly designed technical solution fails because it doesn't fit the organization's culture

None of these are technical problems. No amount of better database design or cleaner code will solve them. They're complex, human, organizational problems. And here's the kicker: **these complex factors determine project success or failure far more often than technical excellence does.**

This was my revelation. My deep analysis skills were perfect for the complicated technical domain. But I was trying to use the same approach — analyse deeper, plan better, document more thoroughly—for complex problems where that approach fundamentally doesn't work.

In complex systems, you can't analyse your way to a solution. The system will surprise you. People will surprise you. The context will shift. What you need aren't better analysis tools—you need different tools entirely. You need approaches that embrace uncertainty, that learn from feedback, that adapt to emergence.

This is where frameworks like the [Cynefin framework](https://thecynefin.co/about-us/about-cynefin-framework/) and understanding [wicked problems](https://en.wikipedia.org/wiki/Wicked_problem) become invaluable—topics I'll explore in depth in future posts. But the core insight is this: **To succeed as a software engineer in enterprise environments, you need tools for both complicated *and* complex domains.**

No engineering training prepares you for complexity. But it's complexity, not technical challenges, that determines whether your projects succeed. 

## Agile to the Rescue

This is where systems thinking illuminated agile for me in a completely new way.

I'd been practising "agile" for years at this point — stand ups, sprints, retrospectives, the whole ceremony. But honestly, I never fully understood *why* it worked (when it did work) or why it so often felt like we were just going through the motions.

Understanding complex systems changed that.

In complex systems, you can't analyse your way to a solution because the system is constantly adapting and changing. Traditional plan-driven approaches assume you can understand the problem space upfront, design a solution, and execute it. That works brilliantly for complicated problems. But for complex problems, you need a different approach entirely.

You need to **probe-sense-respond**.

Here's what that means: Instead of trying to analyse everything upfront, you try something small (probe), observe what actually happens rather than what you predicted would happen (sense), and then adjust your approach based on what you learned (respond). Then you repeat the cycle.

Sound familiar? It should.

[Dave Thomas](https://pragdave.me/thoughts/active/2014-03-04-time-to-kill-agile.html), one of the original signatories of the Agile Manifesto, gave a talk called "[Agile is Dead](https://www.youtube.com/watch?v=a-BOSpxYJ9M)" (the title is deliberately provocative—he wasn't rejecting agile, but reclaiming it from the prescriptive methodology industry that had grown up around it). In it, he distilled agile down to three simple steps:

1. Find out where you are
2. Take a small step toward your goal  
3. Adjust your understanding based on what you learned

Then repeat.

Look at the parallel:

**Navigating complex systems:** probe → sense → respond

**Dave Thomas's agility:** find where you are → take small step → learn and adjust

This isn't a coincidence. Agile practices emerged as a response to the reality that software projects exist in complex environments. They're not just technical challenges—they're socio-technical systems involving people, organizations, changing requirements, and emergent behaviors.

Suddenly, agile made sense to me at a fundamental level. It wasn't a methodology to follow dogmatically. It was an approach for navigating complexity. The sprint cycles, the frequent feedback, the retrospectives, the emphasis on working software over comprehensive documentation—all of these practices are tools for operating in complex environments where you can't predict outcomes upfront.

Systems thinking gave me the theoretical foundation that I needed to truly understand agile. More importantly, it gave me a framework for knowing *when* to use agile approaches (complex problems) versus when more traditional analysis-driven approaches make sense (complicated problems). 


## Conclusion

Here's the reality: we software engineers love being technical experts. We love mastering design patterns, learning new languages, optimizing performance, building elegant architectures. And all of that matters—it absolutely does. Technical excellence is the foundation of everything we do.

But here's what I've learned over three decades in this field: **technical excellence alone isn't enough**, especially in enterprise environments. The projects that fail rarely fail because of technical inadequacy. They fail because of misaligned stakeholder expectations, organizational resistance to change, unclear problem definitions, shifting priorities, or poor communication across teams.

They fail because of complexity.

To succeed as a software engineer, you need skills in both domains. You need the analytical rigor to solve complicated technical problems. And you need the systems thinking approaches to navigate complex organizational and human challenges.

That's what being a "systemic programmer" means to me. It's about recognizing that software engineering isn't just a technical discipline—it's a socio-technical one. It's about having tools for both the complicated and the complex. It's about knowing when to analyze deeply and when to probe and learn.

This blog exists to explore both sides of that equation. Yes, I'll write about technical topics—I love that stuff and always will. But I'll also write about systems thinking, complexity, organizational dynamics, and the human side of software engineering. Expect deep dives into topics like:

- The Cynefin framework and how to apply it to software projects
- Understanding and working with wicked problems  
- Systems thinking patterns for software engineers
- Navigating organizational complexity
- The intersection of technical and social systems

I'm still learning. After 30 years, I'm more aware than ever of how much I don't know. That's why I'd love to hear from you—your experiences, your insights, your challenges. What complexity challenges have you faced? What tools or frameworks have helped you navigate them?

Get in touch via email, LinkedIn, or by commenting on these posts. Let's learn together.

Thanks for joining me on this journey. Welcome to The Systemic Programmer. 

## References

1. **[Agile Manifesto](https://agilemanifesto.org)** - The original 2001 Manifesto for Agile Software Development, signed by Kent Beck, Martin Fowler, Robert C. Martin, Dave Thomas, and 13 others.
2. **[Rational Unified Process](https://en.wikipedia.org/wiki/Rational_unified_process)** - Wikipedia's comprehensive overview of RUP, the iterative software development process framework created by Rational Software. Covers the four phases (Inception, Elaboration, Construction, Transition) and six best practices.
3. **[Master's in Software Engineering - Open University](https://www.open.ac.uk/postgraduate/qualifications/f66)** - The MSc in Computing with specialization in Software Engineering program at The Open University, Milton Keynes, UK.
4. [Systems Thinking in Practice (TU811)](https://www.open.ac.uk/postgraduate/modules/tb871/)** - Open University module introducing systems thinking approaches and their practical application.
5. **[Systems Thinking (TU872)](https://www.open.ac.uk/postgraduate/modules/tb872/)** - Advanced Open University module on systems thinking theory and practice.
6. **[The Cynefin Framework](https://thecynefin.co/about-us/about-cynefin-framework/)** - Official resource from The Cynefin Company (founded by Dave Snowden) about the framework for decision-making in complex contexts. Distinguishes between simple, complicated, complex, and chaotic domains.
7. **[Wicked Problems](https://en.wikipedia.org/wiki/Wicked_problem)** - Wikipedia article on wicked problems—problems that are difficult or impossible to solve due to incomplete, contradictory, and changing requirements. Based on the original 1973 work by Horst Rittel and Melvin M. Webber.
8. **[Agile is Dead (Long Live Agility)](https://pragdave.me/thoughts/active/2014-03-04-time-to-kill-agile.html)** - Dave Thomas's influential 2014 blog post arguing that the word "agile" has been corrupted by the methodology industry, and calling for a return to agility as a set of principles rather than prescribed practices.
9. **[Agile is Dead - GOTO 2015 Talk](https://www.youtube.com/watch?v=a-BOSpxYJ9M)** - Dave Thomas's conference presentation expanding on his "Agile is Dead" blog post, distilling agility down to: find where you are, take a small step, adjust based on what you learned, and repeat.
10. **[Systems Thinking](https://en.wikipedia.org/wiki/Systems_thinking)** - Wikipedia's comprehensive overview of systems thinking as a holistic approach to understanding complex systems. Covers history, characteristics, frameworks, and key contributors including Peter Senge, Donella Meadows, and Russell Ackoff. 
