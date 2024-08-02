<?php

namespace App\Services;

use GuzzleHttp\Client;

class ChatGptService
{
    public $interviewTexts = [
        [
            [
                'role' => "system",
                'content' => "I will provide you with one resume and one job post. Please act as the interviewer and ask questions based on the provided information. I will respond to your questions. This will serve as an interview conversation simulation."
            ]
        ],
        [
            [
                'role' => 'system',
                'content' => "Here is the information below, you should ask me 10 question one by one, the question could be adjust base on what did I answered.
                Resume:
                
                Jackie Burchett Uppal Building Supplies
                Nanaimo, British Columbia, Canada
                Summary
                I am a collaborative team player who values open communication and feedback, and I believe a positive and supportive work environment is essential for success.
                Experience
                Uppal Building Supplies
                Branch Manager
                February 2023 - Present (1 year 5 months) Nanaimo, British Columbia, Canada
                Persona Hair Group
                Senior Stylist/Assistant Manager
                October 2011 - July 2022 (10 years 10 months)
                Shoppers Drug Mart
                Cosmetic Merchandiser/Receiver August 2010 - January 2011 (6 months)
                Mexx Canada Company
                Retail Associate
                May 2008 - January 2010 (1 year 9 months)
                Education
                Douglas College
                Virtual Customer Service and Customer Service Skills · (December 2022 - March 2023)
                Blanche Macdonald Centre
                Diploma, Global Makeup Artistry · (2010 - 2011)
                Vancouver Island University
                Diploma, Hairdressing · (2007 - 2008)
                
                Contact
                www.linkedin.com/in/jackie- burchett-8626374a (LinkedIn)
                www.facebook.com/jackiebMUA
                (Portfolio)
                Top Skills
                Direct Sales
                Supply Chain Management Operations Management
                Certifications
                Microsoft Office 365
                
                
                
                Job:
                Job details
                Here’s how the job details align with your profile.
                Pay
                $70,000–$80,000 a year
                Job type
                Full-time
                Permanent
                Shift and schedule
                Monday to Friday
                &nbsp;
                Location
                Port Coquitlam, BC
                &nbsp;
                Benefits
                Pulled from the full job description
                Casual dress
                Company events
                Dental care
                Disability insurance
                Employee assistance program
                Extended health care
                Life insurance
                &nbsp;
                Full job description
                Sales Executive at Wesgar Inc.
                
                Company Description:
                Wesgar has the capacity to take care of all your precision sheet metal fabrication needs in our world-class, 80,000 square feet plant, located on Wesgar's four-acre campus. Our one-stop shop meets every need from concept to a superior finished product. Wesgar combines unique design for manufacturability and prototype fabrication with custom manufacturing, screen printing, powder coating, wet painting, product assembly, machining, quality inspection and documentation, and inventory management.
                
                Job Summary:
                We are seeking a highly motivated and experienced Sales Executive to join our team. The ideal candidate will be responsible for both generating new business and managing existing accounts. This role involves a blend of cold calling, lead generation, account management, and strategic growth initiatives.
                
                Key Responsibilities:
                
                Lead Generation and Cold Calling:
                
                Identify and target potential clients through research and outreach.
                Develop and execute effective cold calling strategies to generate new business opportunities.
                Maintain a robust pipeline of prospects and ensure consistent follow-up.
                Ensure diligent tracking of metrics and CRM use
                Account Management:
                
                Serve as the primary point of contact for assigned accounts, ensuring their needs are met with timely and effective solutions.
                Build and maintain strong, long-lasting customer relationships.
                Conduct regular business reviews to assess client satisfaction and identify areas for improvement.
                Strategic Growth:
                
                Develop and implement account plans aimed at maximizing client growth and retention.
                Collaborate with clients to understand their business goals and provide strategic guidance on how our products/services can help achieve them.
                Monitor and analyze account performance metrics, providing insights and recommendations for improvement.
                Cross-Functional Collaboration:
                
                Work closely with the sales, marketing, and product teams to ensure alignment and support for client initiatives.
                Communicate client feedback and market trends to internal stakeholders to inform product development and marketing strategies.
                Reporting and Documentation:
                
                Maintain accurate and up-to-date records of all client interactions, sales activities, and account plans.
                Prepare and present reports on account performance and strategic initiatives to senior management.
                Qualifications:
                
                Bachelor’s degree in Business, Marketing, or a related field.
                Proven experience as an Account Executive, Account Manager, or in a similar role.
                Strong understanding of sales principles and customer service practices.
                Excellent communication, negotiation, and presentation skills.
                Ability to manage multiple accounts while seeking new opportunities.
                Proficiency with CRM software and sales tools.
                Strong analytical and problem-solving skills.
                What We Offer:
                
                Competitive salary and commission structure.
                Comprehensive benefits package, including extended health and dental.
                Opportunities for professional growth and career advancement.
                A dynamic and supportive work environment.
                Private Office (office based position)
                Job Types: Full-time, Permanent
                
                Pay: $70,000.00-$80,000.00 per year
                
                Benefits:
                
                Casual dress
                Company events
                Dental care
                Disability insurance
                Employee assistance program
                Extended health care
                Life insurance
                On-site parking
                Paid time off
                Schedule:
                
                Monday to Friday
                Supplemental pay types:
                
                Commission pay
                Education:
                
                Bachelor's Degree (preferred)
                Experience:
                
                sales: 5 years (required)
                Licence/Certification:
                
                Driving Licence (preferred)
                Work Location: In person"
            ]
        ],
        // q1
        [
            [
                'role' => "user",
                'content' => "Thank you for having me. As Branch Manager at Uppal Building Supplies, I prioritize open communication and team collaboration. I hold regular meetings to discuss ideas and concerns and recognize individual and team achievements to keep motivation high. Additionally, I provide training opportunities for professional growth. Setting clear, achievable goals and involving the team in the process ensures everyone understands their role in our success. This approach has helped us consistently meet or exceed our business objectives."
            ]
        ],
        // q2
        [
            [
                'role' => "user",
                'content' => "Thank you. One significant challenge I faced at Uppal Building Supplies was a sudden drop in sales during a seasonal slump. To address this, I organized a targeted marketing campaign and introduced special promotions to attract customers. I also restructured our inventory management to better align with customer demand. By quickly adapting and involving the team in these strategies, we were able to recover and exceed our sales targets for that period."
            ]
        ],
        // q3
        [
            [
                'role' => "user",
                'content' => "Thank you. My expertise in direct sales and operations management will be highly beneficial in the Sales Executive role at Wesgar Inc. For lead generation, my experience in targeted marketing and customer engagement will help identify and attract potential clients effectively. In account management, my background in building strong customer relationships and managing team dynamics will ensure that client needs are met and exceeded, fostering long-term partnerships and driving sales growth."
            ]
        ],
        // q4
        [
            [
                'role' => "user",
                'content' => "Certainly. At Uppal Building Supplies, I implemented a personalized follow-up strategy for key accounts, ensuring regular check-ins and addressing any concerns promptly. This approach strengthened client relationships and led to a 20% increase in repeat business within six months."
            ]
        ],
        // q5
        [
            [
                'role' => "user",
                'content' => "Thank you. I am proficient with Salesforce and HubSpot CRM. At Uppal Building Supplies, I used Salesforce to track leads, manage customer interactions, and streamline the sales process. HubSpot helped in automating marketing efforts and improving customer follow-ups. These tools enhanced our sales efficiency and improved customer relationship management, contributing to a 15% boost in sales."
            ]
        ],
        // q6
        [
            [
                'role' => "user",
                'content' => "Certainly. When managing a high volume of new leads while maintaining service quality for existing accounts, I prioritize efficient time management and delegation. I segmented the leads based on priority and potential, using CRM tools to automate follow-ups for lower-priority leads. For existing accounts, I ensured dedicated time for regular check-ins and personalized service. This balance allowed us to convert new leads effectively while keeping our existing clients satisfied."
            ]
        ],
        // q7
        [
            [
                'role' => "user",
                'content' => "At Uppal Building Supplies, I grew a key account by introducing tailored service packages and regular check-ins. This approach addressed their specific needs, leading to a 30% increase in their purchases and significantly improving their satisfaction and retention rate."
            ]
        ],
        // q8
        [
            [
                'role' => "user",
                'content' => "Sure. I used a research-based cold calling strategy, where I gathered key information about prospects before calling. This allowed me to tailor my pitch to their specific needs and interests. By personalizing each call, I achieved a 25% conversion rate, significantly higher than the average cold calling success rate."
            ]
        ],
        // q9
        [
            [
                'role' => "user",
                'content' => "Certainly. At Persona Hair Group, I collaborated with the marketing team to create promotional campaigns for new services. We held joint meetings to align our strategies and share insights. This collaboration resulted in a 15% increase in bookings and a successful launch of our new service offerings."
            ]
        ],
        // q10
        [
            [
                'role' => "user",
                'content' => "Of course. I approach learning new products or services by first thoroughly researching and understanding the product details and benefits. I then participate in any available training sessions and seek insights from colleagues with experience in the area. Additionally, I continuously update my knowledge by staying informed about industry trends and customer feedback. This methodical approach ensures I can effectively communicate the value of our products and services to clients."
            ]
        ],
        // result
        [
            [
                'role' => "system",
                'content' => "Please give this candidate some comment and rating scores on his/her interview."
            ]
        ],
    ];

    public function generateText($messageArrs)
    {
        $apiKey = env('GPT_API_KEY');
        $client = new Client();
        $response = $client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer ${apiKey}",
            ],
            'json' => [
                'model' => 'gpt-4o-mini',
                'messages' => $messageArrs
            ],
        ]);

        $result = json_decode($response->getBody(), true);
        return $result['choices'][0]['message']['content'];
    }
}
